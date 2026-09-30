import { test, before, after } from 'node:test';
import assert from 'node:assert/strict';
import { BASE, launch, visitor, jsErrors } from './helpers.mjs';

let browser;
before(async () => {
	browser = await launch();
});
after(() => browser?.close());

test('home: sin errores de JS, con js en <html> y datos estructurados válidos', async () => {
	const ctx = await visitor(browser);
	const page = await ctx.newPage();
	const errors = jsErrors(page);
	await page.goto(BASE + '/', { waitUntil: 'networkidle' });
	assert.ok(await page.evaluate(() => document.documentElement.classList.contains('js')), 'falta la clase js');
	const schemas = await page.locator('script[type="application/ld+json"]').allTextContents();
	assert.ok(schemas.length > 0, 'no hay JSON-LD');
	for (const s of schemas) {
		assert.doesNotThrow(() => JSON.parse(s));
	}
	assert.equal(await page.locator('h1').count(), 1, 'la home lleva un solo h1');
	assert.deepEqual(errors, []);
	await ctx.close();
});

test('head limpio: sin emojis, generator ni enlaces que el sitio no usa', async () => {
	const ctx = await visitor(browser);
	const html = await (await ctx.request.get(BASE + '/')).text();
	for (const needle of ['wp-emoji', 'name="generator"', 'rel="EditURI"', 'rel="shortlink"', 'wlwmanifest']) {
		assert.ok(!html.includes(needle), `sobra ${needle}`);
	}
	await ctx.close();
});

test('mobile: sin scroll lateral en las páginas principales', async () => {
	const ctx = await visitor(browser, { width: 390, height: 844 });
	const page = await ctx.newPage();
	for (const path of ['/', '/contacto/', '/servicios/', '/productos/guia-de-facturacion-electronica/']) {
		await page.goto(BASE + path, { waitUntil: 'load' });
		const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
		assert.equal(overflow, false, `scroll lateral en ${path}`);
	}
	await ctx.close();
});

test('compartir: el botón de copiar aparece antes de pintar (sin salto de layout)', async () => {
	const ctx = await visitor(browser);
	const page = await ctx.newPage();
	const [post] = await (await ctx.request.get(BASE + '/?rest_route=/wp/v2/posts&per_page=1&_fields=link')).json();
	assert.ok(post?.link, 'no hay notas en el contenido de demo');
	// Se frena main.js: lo que se ve tiene que venir del script en línea, antes de pintar.
	await ctx.route(/themes\/feelolab\/assets\/js\/main\.js/, (r) => r.abort());
	await page.goto(post.link, { waitUntil: 'load' });
	assert.equal(await page.locator('.share').count(), 1);
	assert.equal(await page.locator('.share__copy').isVisible(), true);
	await ctx.close();
});
