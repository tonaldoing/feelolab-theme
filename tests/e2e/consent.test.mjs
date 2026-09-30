import { test, before, after } from 'node:test';
import assert from 'node:assert/strict';
import { BASE, launch, visitor, admin, devScript, dataLayer, jsErrors } from './helpers.mjs';

let browser;
let adminCtx;
before(async () => {
	browser = await launch();
	({ ctx: adminCtx } = await admin(browser));
	await devScript(adminCtx, 'set-tracking.php?on=1');
});
after(async () => {
	await devScript(adminCtx, 'set-tracking.php?on=0');
	await browser?.close();
});

test('banner de cookies: Consent Mode arranca en denegado y "Aceptar" lo actualiza', async () => {
	const ctx = await visitor(browser, { width: 390, height: 844 });
	const page = await ctx.newPage();
	const errors = jsErrors(page);
	await page.goto(BASE + '/', { waitUntil: 'load' });
	await page.locator('#feelo-consent').waitFor({ state: 'visible' });

	const initial = (await dataLayer(page)).filter((s) => s.startsWith('consent default'));
	assert.equal(initial.length, 1);
	assert.match(initial[0], /"analytics_storage":"denied"/);
	assert.match(initial[0], /"ad_storage":"denied"/);

	await page.getByRole('button', { name: /Aceptar|Accept/ }).click();
	assert.equal(await page.locator('#feelo-consent').isVisible(), false);
	const cookie = (await ctx.cookies()).find((c) => c.name === 'feelo_consent');
	assert.equal(cookie?.value, 'granted');
	assert.ok((await dataLayer(page)).some((s) => s.startsWith('consent update') && s.includes('"analytics_storage":"granted"')));

	// Con la cookie, la página siguiente arranca concedida y sin banner.
	await page.reload({ waitUntil: 'load' });
	assert.equal(await page.locator('#feelo-consent').isVisible(), false);
	assert.ok((await dataLayer(page)).some((s) => s.startsWith('consent update')));
	assert.deepEqual(errors, []);
	await ctx.close();
});

test('banner de cookies: se reabre desde el pie y "Rechazar" guarda denegado', async () => {
	const ctx = await visitor(browser, { cookies: [{ name: 'feelo_consent', value: 'granted' }] });
	const page = await ctx.newPage();
	await page.goto(BASE + '/', { waitUntil: 'load' });
	await page.locator('[data-feelo-consent-open]').first().click();
	await page.locator('#feelo-consent').waitFor({ state: 'visible' });
	await page.getByRole('button', { name: /Rechazar|Reject/ }).click();
	const cookie = (await ctx.cookies()).find((c) => c.name === 'feelo_consent');
	assert.equal(cookie?.value, 'denied');
	await ctx.close();
});

test('eventos: WhatsApp, teléfono y email se miden con su ubicación', async () => {
	const ctx = await visitor(browser, { cookies: [{ name: 'feelo_consent', value: 'granted' }] });
	const page = await ctx.newPage();
	await page.goto(BASE + '/', { waitUntil: 'load' });
	// Sin salir de la página: se frena la navegación después de que el script mide.
	await page.evaluate(() =>
		document.addEventListener('click', (e) => {
			if (e.target.closest('a[href^="https://wa.me"],a[href^="tel:"],a[href^="mailto:"]')) {
				e.preventDefault();
			}
		})
	);
	for (const sel of ['a[href^="https://wa.me"]', 'a[href^="tel:"]', 'a[href^="mailto:"]']) {
		await page.locator(sel).first().evaluate((el) => el.click());
	}
	const events = (await dataLayer(page)).filter((s) => /click_(whatsapp|phone|email)/.test(s));
	for (const name of ['click_whatsapp', 'click_phone', 'click_email']) {
		assert.ok(events.some((s) => s.includes(name)), `falta ${name}`);
	}
	assert.ok(events.every((s) => s.includes('"feelo_location"')), 'los eventos llevan feelo_location');
	await ctx.close();
});

test('eventos: generate_lead se envía una vez por envío, no al recargar', async () => {
	const ctx = await visitor(browser, { cookies: [{ name: 'feelo_consent', value: 'granted' }] });
	const page = await ctx.newPage();
	const leads = async () => (await dataLayer(page)).filter((s) => s.includes('generate_lead')).length;
	await page.goto(BASE + '/contacto/?feelo_form=ok&feelo_t=e2eaaaaa', { waitUntil: 'load' });
	assert.equal(await leads(), 1);
	await page.reload({ waitUntil: 'load' });
	assert.equal(await leads(), 0);
	await page.goto(BASE + '/contacto/?feelo_form=ok&feelo_t=e2ebbbbb', { waitUntil: 'load' });
	assert.equal(await leads(), 1);
	await ctx.close();
});

test('sin banner ni medición configurados, no se carga ningún script propio', async () => {
	await devScript(adminCtx, 'set-tracking.php?on=0');
	try {
		const ctx = await visitor(browser);
		const html = await (await ctx.request.get(BASE + '/')).text();
		assert.ok(!html.includes('consent.js'), 'consent.js no debería cargarse');
		assert.ok(!html.includes('tracking.js'), 'tracking.js no debería cargarse');
		assert.ok(!html.includes('id="feelo-consent"'), 'el banner no debería estar');
		await ctx.close();
	} finally {
		await devScript(adminCtx, 'set-tracking.php?on=1');
	}
});
