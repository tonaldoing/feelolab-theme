/**
 * Utilidades de la suite e2e. Corre contra un WordPress de Playground ya levantado
 * (`npm run dev`, con el contenido de demo): `npm run test:e2e`.
 *
 * - BASE: dirección del sitio (por defecto http://127.0.0.1:9400).
 * - CHROME_PATH: navegador a usar. Por defecto el Chrome del sistema.
 */
import { chromium } from 'playwright-core';
import fs from 'node:fs';

export const BASE = process.env.BASE || 'http://127.0.0.1:9400';

const CANDIDATES = [
	process.env.CHROME_PATH,
	'/usr/bin/google-chrome',
	'/usr/bin/chromium',
	'/usr/bin/chromium-browser',
	'/opt/pw-browsers/chromium-1194/chrome-linux/chrome',
].filter(Boolean);

export async function launch() {
	const executablePath = CANDIDATES.find((p) => fs.existsSync(p));
	if (!executablePath) {
		throw new Error('No encontré Chrome: definí CHROME_PATH.');
	}
	return chromium.launch({ executablePath, args: ['--no-sandbox'] });
}

/** Visitante anónimo: Playground inicia sesión solo en la primera visita salvo con esta cookie. */
export async function visitor(browser, { width = 1280, height = 900, cookies = [] } = {}) {
	const ctx = await browser.newContext({ viewport: { width, height } });
	await ctx.addCookies([{ name: 'playground_auto_login_already_happened', value: '1', url: BASE }, ...cookies.map((c) => ({ url: BASE, ...c }))]);
	// Nada sale a terceros durante los tests.
	await ctx.route(/googletagmanager|google-analytics|google\.com\/maps|challenges\.cloudflare/, (r) => r.abort());
	return ctx;
}

/** Administradora: el blueprint trae `login: true`, la primera visita al panel inicia sesión. */
export async function admin(browser) {
	const ctx = await browser.newContext({ viewport: { width: 1400, height: 1000 } });
	const page = await ctx.newPage();
	await page.goto(BASE + '/wp-admin/');
	await page.waitForURL(/wp-admin/);
	return { ctx, page };
}

/** Scripts de dev/ montados en /wordpress/dev: cargan opciones de prueba. */
export async function devScript(ctx, path) {
	const res = await ctx.request.get(BASE + '/dev/' + path);
	if (!res.ok()) {
		throw new Error(`dev/${path}: ${res.status()}`);
	}
	return res.text();
}

/** dataLayer como texto ("consent default {...}", "event click_whatsapp {...}"). */
export function dataLayer(page) {
	return page.evaluate(() =>
		(window.dataLayer || []).map((a) =>
			Array.from(a && typeof a === 'object' && 'length' in a ? a : [a])
				.map((x) => (typeof x === 'object' ? JSON.stringify(x) : String(x)))
				.join(' ')
		)
	);
}

/** Junta los errores de JavaScript de una página. */
export function jsErrors(page) {
	const errors = [];
	page.on('pageerror', (e) => errors.push(e.message));
	return errors;
}
