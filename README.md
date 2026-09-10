# juanto.net

WordPress block theme (`wp/wp-content/themes/juanto`) and core plugin (`wp/wp-content/plugins/juanto-core`) for [juanto.net](https://juanto.net), the portfolio of Juan Pérez, design engineer.

- **Plugin:** the `project` content type (case / card / lab), dynamic blocks, the "now building" status bar and SEO.
- **Theme:** templates, design tokens (`theme.json`) and self-hosted type.

## Develop
    nvm use && (cd wp/wp-content/plugins/juanto-core && npm ci && npm run build)
    composer install && composer test && composer lint

License: GPL-2.0-or-later.
