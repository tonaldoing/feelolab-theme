#!/usr/bin/env bash
# Arma la versión para wordpress.org del tema y del plugin en dist/wporg/.
#
# Es la misma base de código menos lo que el directorio no permite: el actualizador propio
# (feelolab-core/src/Updater.php, feelolab/inc/updates.php) y la pantalla Versiones
# (feelolab-core/src/Versions.php), porque ahí las actualizaciones las da WordPress. El código
# común ya funciona sin esos archivos (class_exists / is_readable). La captura del tema es una
# imagen del tema mismo (dev/wporg/screenshot.png, 1200×900), como pide el directorio.
#
# Uso: dev/build-wporg.sh  → dist/wporg/feelolab, dist/wporg/feelolab-core y sus .zip
set -euo pipefail
cd "$(dirname "$0")/.."
OUT="dist/wporg"
rm -rf "$OUT"
mkdir -p "$OUT"

copy() { # origen destino [excluidos...]
	local src="$1" dst="$2"
	shift 2
	cp -a "$src" "$dst"
	for x in "$@"; do rm -f "$dst/$x"; done
	find "$dst" -name '.DS_Store' -delete
}

copy feelolab "$OUT/feelolab" inc/updates.php screenshot.png
cp dev/wporg/screenshot.png "$OUT/feelolab/screenshot.png"

copy feelolab-core "$OUT/feelolab-core" src/Updater.php src/Versions.php
# El párrafo del readme que describe el chequeo de versiones en GitHub no aplica.
php -r '
	$f = $argv[1];
	$t = file_get_contents( $f );
	$t = preg_replace( "/\n\nGitHub \(updates\):[^\n]*/", "", $t );
	file_put_contents( $f, $t );
' "$OUT/feelolab-core/readme.txt"

# "Stable tag" = la versión que se publica (así no hay que acordarse de un quinto lugar).
VERSION=$(grep -m1 '^Version:' feelolab/style.css | awk '{print $2}')
case "$VERSION" in *-*) echo "La versión $VERSION es de prueba: wordpress.org solo recibe estables." >&2; exit 1 ;; esac
sed -i "s/^Stable tag: .*/Stable tag: $VERSION/" "$OUT/feelolab/readme.txt" "$OUT/feelolab-core/readme.txt"

# Nada del actualizador puede quedar.
if grep -rqE "api\.github\.com|feelolab-releases|pre_set_site_transient_update" "$OUT"; then
	echo "Quedó código del actualizador:" >&2
	grep -rnE "api\.github\.com|feelolab-releases|pre_set_site_transient_update" "$OUT" >&2
	exit 1
fi
find "$OUT" -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null

( cd "$OUT" && zip -qr feelolab.zip feelolab && zip -qr feelolab-core.zip feelolab-core )
echo "Listo: $OUT/feelolab.zip y $OUT/feelolab-core.zip"
