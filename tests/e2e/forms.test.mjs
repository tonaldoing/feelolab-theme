import { test, before, after } from 'node:test';
import assert from 'node:assert/strict';
import { BASE, launch, visitor, admin, devScript, jsErrors } from './helpers.mjs';

let browser;
let adminPage;
before(async () => {
	browser = await launch();
	const a = await admin(browser);
	adminPage = a.page;
	await devScript(a.ctx, 'e2e-setup.php');
});
after(() => browser?.close());

/** Mensajes guardados cuyo título contiene el texto (Mensajes → buscar). */
async function stored(text) {
	await adminPage.goto(`${BASE}/wp-admin/edit.php?post_type=feelo_mensaje&post_status=all&s=${encodeURIComponent(text)}`);
	return adminPage.locator('#the-list tr:not(.no-items)').count();
}

async function fillContact(page, name) {
	await page.fill('#feelo-nombre', name);
	await page.fill('#feelo-email', 'e2e@example.com');
	await page.fill('#feelo-mensaje', 'Mensaje de prueba de la suite e2e.');
	if (await page.locator('#feelo-acepto').count()) {
		await page.check('#feelo-acepto');
	}
}

test('contacto: un envío real se guarda en Mensajes y muestra la confirmación con foco', async () => {
	const name = 'E2E Persona ' + Date.now();
	const ctx = await visitor(browser);
	const page = await ctx.newPage();
	const errors = jsErrors(page);
	await page.goto(BASE + '/contacto/', { waitUntil: 'load' });
	await fillContact(page, name);
	await page.waitForTimeout(3500); // Tiempo mínimo de llenado del antispam.
	await page.locator('.feelo-form button[type=submit]').click();
	await page.waitForURL(/feelo_form=ok/);
	const notice = page.locator('.feelo-form__notice--ok');
	await notice.waitFor({ state: 'visible' });
	assert.equal(await page.evaluate(() => document.activeElement?.classList.contains('feelo-form__notice')), true, 'el foco va al aviso');
	assert.equal(await stored(name), 1);
	assert.deepEqual(errors, []);
	await ctx.close();
});

test('contacto: un bot que envía en menos de 3 segundos ve "ok" pero no se guarda nada', async () => {
	const name = 'E2E Bot ' + Date.now();
	const ctx = await visitor(browser);
	const page = await ctx.newPage();
	await page.goto(BASE + '/contacto/', { waitUntil: 'load' });
	await fillContact(page, name);
	await page.locator('.feelo-form button[type=submit]').click();
	await page.waitForURL(/feelo_form=ok/);
	assert.equal(await stored(name), 0);
	await ctx.close();
});

test('contacto: el honeypot descarta el envío', async () => {
	const name = 'E2E Honeypot ' + Date.now();
	const ctx = await visitor(browser);
	const page = await ctx.newPage();
	await page.goto(BASE + '/contacto/', { waitUntil: 'load' });
	await fillContact(page, name);
	await page.locator('#feelo-website').evaluate((el) => {
		el.value = 'http://spam.example';
	});
	await page.waitForTimeout(3500);
	await page.locator('.feelo-form button[type=submit]').click();
	await page.waitForURL(/feelo_form=ok/);
	assert.equal(await stored(name), 0);
	await ctx.close();
});

test('contacto: sin JavaScript, los errores del servidor vuelven con los datos cargados', async () => {
	const ctx = await browser.newContext({ javaScriptEnabled: false });
	await ctx.addCookies([{ name: 'playground_auto_login_already_happened', value: '1', url: BASE }]);
	const page = await ctx.newPage();
	await page.goto(BASE + '/contacto/', { waitUntil: 'load' });
	await page.fill('#feelo-nombre', 'E2E Sin JS');
	// Email inválido para el servidor pero aceptado por el navegador.
	await page.fill('#feelo-email', 'nombre@dominio');
	await page.fill('#feelo-mensaje', 'Mensaje de prueba.');
	// Sin JS, la espera de "elemento estable" de Playwright no resuelve: se hace clic directo.
	if (await page.locator('#feelo-acepto').count()) {
		await page.check('#feelo-acepto', { force: true });
	}
	await page.waitForTimeout(3500);
	await page.locator('.feelo-form button[type=submit]').click({ force: true });
	await page.waitForURL(/feelo_form=error/);
	assert.equal(await page.locator('.feelo-form__notice--error').count(), 1);
	assert.equal(await page.inputValue('#feelo-nombre'), 'E2E Sin JS');
	assert.equal(await page.getAttribute('#feelo-email', 'aria-invalid'), 'true');
	await ctx.close();
});

test('newsletter: sin proveedor, la suscripción queda guardada en Mensajes', async () => {
	const email = `e2e-${Date.now()}@example.com`;
	const ctx = await visitor(browser);
	const page = await ctx.newPage();
	await page.goto(BASE + '/e2e-newsletter/', { waitUntil: 'load' });
	const form = page.locator('.feelo-news').first();
	await form.locator('input[type=email]').fill(email);
	const consent = form.locator('input[type=checkbox]');
	if (await consent.count()) {
		await consent.check();
	}
	await page.waitForTimeout(2500);
	await form.locator('button[type=submit]').click();
	await page.waitForURL(/feelo_news=ok/);
	assert.equal(await page.locator('.feelo-news__notice--ok').count(), 1);
	assert.equal(await stored(email), 1);
	await ctx.close();
});
