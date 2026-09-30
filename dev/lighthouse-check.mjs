/**
 * Control de Lighthouse para el CI: corre sobre el WordPress de Playground y falla si una página
 * baja de los mínimos. Uso: node dev/lighthouse-check.mjs (con Playground en el puerto 9400).
 */
import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';

const BASE = 'http://127.0.0.1:9400';
const PAGES = [ '/', '/servicios/liquidacion-de-impuestos/', '/productos/guia-de-facturacion-electronica/', '/contacto/' ];
// Performance con margen: los runners de CI son más lentos y variables que una máquina real.
const MIN = { performance: 85, accessibility: 100, seo: 100, 'best-practices': 95 };
let failed = false;

for ( const path of PAGES ) {
	execFileSync( 'npx', [ 'lighthouse', BASE + path, '--quiet', '--output=json', '--output-path=lh.json',
		'--chrome-flags=--headless=new --no-sandbox',
		'--only-categories=performance,accessibility,seo,best-practices',
		'--extra-headers={"Cookie":"playground_auto_login_already_happened=1"}' ], { stdio: 'inherit' } );
	const report = JSON.parse( readFileSync( 'lh.json', 'utf8' ) );
	const scores = Object.fromEntries( Object.entries( report.categories ).map( ( [ k, v ] ) => [ k, Math.round( v.score * 100 ) ] ) );
	const cls = report.audits[ 'cumulative-layout-shift' ].numericValue;
	const low = Object.entries( MIN ).filter( ( [ k, min ] ) => scores[ k ] < min ).map( ( [ k, min ] ) => `${ k } ${ scores[ k ] } < ${ min }` );
	if ( cls > 0.1 ) {
		low.push( `CLS ${ cls.toFixed( 3 ) } > 0.1` );
	}
	console.log( `${ path } → ${ JSON.stringify( scores ) } CLS ${ cls.toFixed( 3 ) } ${ low.length ? '✗ ' + low.join( ', ' ) : '✓' }` );
	failed = failed || low.length > 0;
}
process.exit( failed ? 1 : 0 );
