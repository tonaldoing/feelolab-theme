import { test, before, after } from 'node:test';
import assert from 'node:assert/strict';
import { AxeBuilder } from '@axe-core/playwright';
import { BASE, launch, visitor, admin, devScript, jsErrors } from './helpers.mjs';

let browser;
let ctx;
let page;
before(async () => {
	browser = await launch();
	({ ctx, page } = await admin(browser));
});
after(() => browser?.close());

async function openCustomizer(section = '') {
	await page.goto(BASE + '/wp-admin/customize.php' + (section ? '?autofocus[section]=' + section : ''), { waitUntil: 'load' });
	await page.waitForFunction(() => document.querySelector('#customize-preview iframe')?.contentDocument?.querySelector('main'), null, { timeout: 60000 });
}
const preview = () => page.frames().find((f) => f.url().includes('customize_changeset_uuid'));
/** Cambia un ajuste y espera a que la vista previa lo refleje (sin publicar nada). */
async function set(id, value, ready) {
	await page.evaluate(([i, v]) => wp.customize(i).set(v), [id, value]);
	await page.waitForFunction(ready, null, { timeout: 30000 });
}

test('servicios: bajada, columnas, fondo y qué muestra cada tarjeta se ven en vivo', async () => {
	await openCustomizer('feelolab_home_servicios');
	const frame = 'document.querySelector("#customize-preview iframe").contentDocument';
	await set('feelolab_home_servicios_text', 'Bajada e2e', new Function(`return ${frame}.querySelector('#servicios .section__lead')?.textContent === 'Bajada e2e'`));
	await set('feelolab_home_servicios_columns', '2', new Function(`return ${frame}.querySelector('#servicios .grid')?.classList.contains('grid--2')`));
	await set('feelolab_home_servicios_bg', 'oscuro', new Function(`return ${frame}.querySelector('#servicios')?.classList.contains('section--dark')`));
	assert.ok((await preview().locator('#servicios .card__price').count()) > 0);
	await set('feelolab_home_servicios_show_price', false, new Function(`return ${frame}.querySelectorAll('#servicios .card').length > 0 && ${frame}.querySelectorAll('#servicios .card__price').length === 0`));
	await set('feelolab_home_servicios_more_text', '', new Function(`return !${frame}.querySelector('#servicios .section__more')`));
	// El panel dice dónde se cargan los servicios.
	const help = await page.locator('#sub-accordion-section-feelolab_home_servicios .customize-section-description').innerText();
	assert.match(help, /publicad/);
});

test('sección sin contenido: aviso en la vista previa, nada en el sitio', async () => {
	await openCustomizer();
	await set('feelolab_home_proyectos_show', true, () => true);
	await page.waitForTimeout(3000);
	assert.ok((await preview().locator('.home-placeholder').count()) > 0, 'la vista previa explica por qué no aparece');
	const v = await visitor(browser);
	const html = await (await v.request.get(BASE + '/')).text();
	assert.ok(!html.includes('home-placeholder'), 'el visitante no ve avisos');
	await v.close();
});

test('tipografía: la de textos se cambia sin tocar la de títulos', async () => {
	await openCustomizer();
	const frame = 'document.querySelector("#customize-preview iframe").contentDocument';
	await set('feelolab_font_pair', 'fraunces', new Function(`return getComputedStyle(${frame}.querySelector('h1')).fontFamily.includes('Fraunces')`));
	const heading = await preview().evaluate(() => getComputedStyle(document.querySelector('h1')).fontFamily);
	await set('feelolab_font_body', 'source-serif-4', new Function(`return getComputedStyle(${frame}.body).fontFamily.includes('Source Serif 4')`));
	assert.equal(await preview().evaluate(() => getComputedStyle(document.querySelector('h1')).fontFamily), heading);
	await set('feelolab_font_heading', 'figtree', new Function(`return getComputedStyle(${frame}.querySelector('h1')).fontFamily.includes('Figtree')`));
	assert.match(await preview().evaluate(() => getComputedStyle(document.body).fontFamily), /Source Serif 4/);
});

test('menú de respaldo: sin menú asignado, los contenidos con publicaciones y Contacto al final', async () => {
	await devScript(ctx, 'e2e-setup.php?menu=sin');
	try {
		const v = await visitor(browser);
		const p = await v.newPage();
		await p.goto(BASE + '/', { waitUntil: 'load' });
		const items = await p.locator('#site-nav-menu .menu > li > a').allInnerTexts();
		assert.equal(items[0], 'Inicio');
		assert.ok(items.includes('Servicios'), items.join(', '));
		assert.equal(items.at(-1), 'Contacto');
		assert.ok(!items.some((i) => /Sample|ejemplo/i.test(i)), 'sin la página de ejemplo');
		assert.equal(await p.locator('#site-nav-menu [aria-current="page"]').innerText(), 'Inicio');
		await v.close();
	} finally {
		await devScript(ctx, 'e2e-setup.php?menu=con');
	}
});

for (const [bg, cls] of [
	['oscuro', 'section--dark'],
	['gris', 'section--surface'],
	['blanco', ''],
]) {
	test(`contraste: todas las secciones de la home con fondo "${bg}" pasan axe`, async () => {
		await devScript(ctx, 'e2e-setup.php?fondos=' + bg);
		try {
			const v = await visitor(browser, { cookies: [{ name: 'feelo_consent', value: 'denied' }] });
			const p = await v.newPage();
			const errors = jsErrors(p);
			await p.goto(BASE + '/', { waitUntil: 'networkidle' });
			if (cls) {
				assert.ok((await p.locator('main section.' + cls).count()) >= 5);
			}
			const result = await new AxeBuilder({ page: p }).withTags(['wcag2a', 'wcag2aa']).analyze();
			assert.deepEqual(result.violations.map((x) => `${x.id}: ${x.nodes.map((n) => n.target.join(' ')).slice(0, 3).join(' | ')}`), []);
			assert.deepEqual(errors, []);
			await v.close();
		} finally {
			await devScript(ctx, 'e2e-setup.php?fondos=normal');
		}
	});
}
