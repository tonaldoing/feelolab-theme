#!/usr/bin/env bash
# Regenera las traducciones del tema y del plugin: .pot, .po (en_US), .mo y .l10n.php.
# Uso: dev/i18n.sh   (WP-CLI en el PATH, o WP="php wp-cli.phar" dev/i18n.sh)
# Después de correrlo, traducir en los .po lo nuevo (msgstr vacío) y volver a correrlo.
set -euo pipefail
cd "$(dirname "$0")/.."
WP="${WP:-wp}"

build() { # carpeta dominio po
	local dir="$1" domain="$2" po="$1/languages/$3"
	$WP i18n make-pot "$dir" "$dir/languages/$domain.pot" --domain="$domain" --skip-js --exclude=vendor,node_modules
	$WP i18n update-po "$dir/languages/$domain.pot" "$po"
	$WP i18n make-mo "$po" "$dir/languages"
	$WP i18n make-php "$po" "$dir/languages"
}

build feelolab feelolab en_US.po
build feelolab-core feelolab-core feelolab-core-en_US.po

# make-php escribe el separador de contexto (msgctxt) como el byte 0x04 crudo dentro de la cadena.
# Es válido, pero Theme Check lo marca como "caracteres no imprimibles": se reescribe como "\4".
for f in feelolab/languages/*.l10n.php feelolab-core/languages/*.l10n.php; do
	php -r '$f=$argv[1]; file_put_contents($f, str_replace("\x04", "'"'"'.\"\\4\".'"'"'", file_get_contents($f)));' "$f"
	php -l "$f" >/dev/null
done
echo "Traducciones regeneradas."
