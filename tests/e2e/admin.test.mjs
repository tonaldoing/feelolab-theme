import { test, before, after } from 'node:test';
import assert from 'node:assert/strict';
import { fileURLToPath } from 'node:url';
import { BASE, launch, admin, devScript, jsErrors } from './helpers.mjs';

const CSV = fileURLToPath(new URL('./fixtures/productos.csv', import.meta.url));

let browser;
let ctx;
let page;
let errors;
before(async () => {
	browser = await launch();
	({ ctx, page } = await admin(browser));
	errors = jsErrors(page);
});
after(() => browser?.close());

async function importCsv() {
	await page.goto(BASE + '/wp-admin/admin.php?page=feelo-importar');
	const another = page.getByRole('link', { name: /Importar otra planilla|Import another/ });
	if (await another.count()) {
		await another.click();
		await page.waitForLoadState('load');
	}
	await page.setInputFiles('#feelo-import-file', CSV);
	await page.getByRole('button', { name: /Subir y revisar|Upload and review/ }).click();
	await page.waitForLoadState('load');
	assert.equal(await page.locator('.feelo-import-table tbody tr').count(), 3, 'la vista previa muestra las 3 filas');
	await page.locator('.feelo-import-start').click();
	await page.locator('.feelo-import-totals').waitFor({ timeout: 120000 });
	return (await page.locator('.feelo-import-totals li').allInnerTexts()).map((t) => parseInt(t, 10));
}

test('importador: la planilla se sube, se revisa y se importa por tandas; la fila sin título es un aviso', async () => {
	const [created, updated, warnings] = await importCsv();
	assert.equal(created + updated, 2);
	assert.equal(warnings, 1);
	assert.equal(await page.locator('.feelo-import-errors li').count(), 1);
});

test('importador: volver a importar la misma planilla actualiza por código, no duplica', async () => {
	const [created, updated] = await importCsv();
	assert.equal(created, 0);
	assert.equal(updated, 2);
	await page.goto(BASE + '/wp-admin/edit.php?post_type=feelo_producto&s=' + encodeURIComponent('Producto de prueba e2e'));
	assert.equal(await page.locator('#the-list tr:not(.no-items)').count(), 1);
});

test('primeros pasos: se ofrece con un aviso en Plugins (sin redirigir) y "no mostrar más" lo apaga', async () => {
	await devScript(ctx, 'e2e-setup.php?wizard=pendiente');
	try {
		await page.goto(BASE + '/wp-admin/plugins.php');
		assert.match(page.url(), /plugins\.php/, 'entrar al panel no redirige al asistente');
		const notice = page.locator('.notice', { hasText: /cinco pasos|five steps/ });
		assert.equal(await notice.count(), 1);
		await notice.getByRole('link', { name: /no mostrar más|don.t show/i }).click();
		await page.waitForLoadState('load');
		await page.goto(BASE + '/wp-admin/plugins.php');
		assert.equal(await page.locator('.notice', { hasText: /cinco pasos|five steps/ }).count(), 0);
	} finally {
		await devScript(ctx, 'e2e-setup.php?wizard=listo');
	}
});

test('primeros pasos: cada paso guarda y avanza, y se puede volver a correr sin duplicar', async () => {
	await page.goto(BASE + '/wp-admin/admin.php?page=feelo-primeros-pasos&paso=2');
	const phone = await page.inputValue('#feelo-w-telefono');
	assert.ok(phone, 'muestra el teléfono ya cargado');
	await page.getByRole('button', { name: /Guardar y seguir|Save and continue/ }).click();
	await page.waitForURL(/paso=3/);
	assert.ok(await page.locator('#feelo-wizard-title').isVisible());
	await page.goto(BASE + '/wp-admin/admin.php?page=feelo-primeros-pasos&paso=2');
	assert.equal(await page.inputValue('#feelo-w-telefono'), phone, 'el dato se conserva');
});

test('Personalizar: el color de marca se ve en vivo y el orden de secciones reordena la home', async () => {
	await page.goto(BASE + '/wp-admin/customize.php', { waitUntil: 'load' });
	await page.waitForFunction(() => window.wp?.customize?.previewer?.preview?.iframe?.[0]?.contentDocument?.querySelector('main'), null, { timeout: 60000 });
	const preview = () => page.frames().find((f) => f.url().includes('customize_changeset_uuid'));
	await page.waitForFunction(() => document.querySelector('#customize-preview iframe'));

	await page.evaluate(() => wp.customize('feelolab_color_primary').set('#c2185b'));
	await page.waitForFunction(
		() => {
			const doc = document.querySelector('#customize-preview iframe').contentDocument;
			return getComputedStyle(doc.documentElement).getPropertyValue('--c-primary').trim().toLowerCase() === '#c2185b';
		},
		null,
		{ timeout: 20000 }
	);

	const ids = () => preview().evaluate(() => [...document.querySelectorAll('main section[id]')].map((s) => s.id));
	const before = await ids();
	const keys = await page.evaluate(() => [...document.querySelectorAll('#customize-control-feelolab_home_order [data-key]')].map((li) => li.dataset.key));
	assert.ok(keys.length > 2, 'el control lista las secciones');
	await page.evaluate((order) => wp.customize('feelolab_home_order').set(order), [...keys].reverse().join(','));
	await page.waitForFunction(
		(first) => {
			const doc = document.querySelector('#customize-preview iframe')?.contentDocument;
			const now = doc ? [...doc.querySelectorAll('main section[id]')].map((s) => s.id) : [];
			return now.length && now[0] !== first;
		},
		before[0],
		{ timeout: 30000 }
	);
	const after = await ids();
	assert.notDeepEqual(after, before);
	// No se publica: la vista previa se descarta al salir.
});

test('Versiones (solo la versión propia, no la de wordpress.org): volver atrás pide confirmación', async (t) => {
	const res = await page.goto(BASE + '/wp-admin/admin.php?page=feelo-versiones');
	if (!res.ok() || (await page.locator('.feelo-hero__title').count()) === 0) {
		t.skip('build sin actualizador');
		return;
	}
	await devScript(ctx, 'set-fake-release.php?on=1');
	try {
		await page.reload({ waitUntil: 'load' });
		const button = page.locator('.feelo-versions button[name=feelo_rollback]').first();
		assert.ok(await button.count(), 'lista versiones anteriores');
		let message = '';
		page.once('dialog', (d) => {
			message = d.message();
			d.dismiss();
		});
		const url = page.url();
		await button.click();
		await page.waitForTimeout(500);
		assert.ok(message.length > 0, 'se pidió confirmación');
		assert.equal(page.url(), url, 'cancelar no envía nada');
	} finally {
		await devScript(ctx, 'set-fake-release.php?on=0');
	}
});

test('pantallas propias del panel: sin errores de JavaScript', async () => {
	for (const screen of ['feelo-ajustes', 'feelo-modulos', 'feelo-lanzamiento', 'feelo-importar', 'feelo-primeros-pasos']) {
		await page.goto(BASE + '/wp-admin/admin.php?page=' + screen, { waitUntil: 'load' });
		assert.equal(await page.locator('.feelo-hero__title').count(), 1, `${screen} lleva la cabecera`);
	}
	assert.deepEqual(errors, []);
});
