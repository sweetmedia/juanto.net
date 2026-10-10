# juanto.net

WordPress block theme (`wp/wp-content/themes/juanto`) and core plugin (`wp/wp-content/plugins/juanto-core`) for [juanto.net](https://juanto.net), the portfolio of Juan Pérez, design engineer.

- **Plugin:** the `project` content type (case / card / lab), dynamic blocks, the "now building" status bar and SEO.
- **Theme:** templates, design tokens (`theme.json`) and self-hosted type.

## Develop
    nvm use && (cd wp/wp-content/plugins/juanto-core && npm ci && npm run build)
    composer install && composer test && composer lint

## Quality gates

**Step 1: Lint and unit tests**

    composer lint && composer test && (cd wp/wp-content/plugins/juanto-core && npm test && npm run lint:js && npm run build)

Expected: 0 PHPCS errors, all PHPUnit and Jest tests pass, and the build succeeds.

**Step 2: Copy and asset rules**

Copy and asset rules are checked by a private script in the project workspace.

    for f in wp/wp-content/plugins/juanto-core/build/blocks/*/view.js; do printf "%s %s\n" "$(gzip -c "$f" | wc -c)" "$f"; done

Expected: every `view.js` under 8 KB gzipped, and all the view scripts on any single page under 30 KB combined.

**Step 3: Lighthouse and axe on the three key pages (mobile)**

    SITE=https://juanto.local:8890
    for p in / /work/nexconnect/ /lab/; do
      npx -y lighthouse "$SITE$p" --quiet --only-categories=performance,accessibility,best-practices,seo --form-factor=mobile --chrome-flags="--headless=new --ignore-certificate-errors" --output=json --output-path=/tmp/lh$(echo $p | tr '/' '_').json
      node -e 'const r=require(process.argv[1]);const a=r.audits;console.log(process.argv[2],"perf",r.categories.performance.score,"a11y",r.categories.accessibility.score,"LCP",a["largest-contentful-paint"].displayValue,"CLS",a["cumulative-layout-shift"].displayValue)' /tmp/lh$(echo $p | tr '/' '_').json $p
    done
    npx -y @axe-core/cli "$SITE/" "$SITE/work/nexconnect/" "$SITE/lab/" --chrome-options="ignore-certificate-errors,headless=new" --exit

Expected: accessibility of 0.95 or higher and 0 axe violations. LCP under 2.0 s and CLS under 0.05. Local MAMP over TLS inflates LCP, so anything under 2.5 s locally is acceptable; staging must meet 2.0 s.

## Deploy notes

- Ship `.htaccess.dist` once per environment: copy it to `.htaccess` on the server (the root `.htaccess` is git-ignored on purpose, so a deploy never overwrites the staging/production rewrite and auth rules already in place there).
- Staging sits behind HTTP basic auth, which breaks WP-Cron's loopback request. Set `DISABLE_WP_CRON` to `true` in that environment's `wp-config.php` and drive cron from a system cron entry instead, for example `* * * * * wp cron event run --due-now --path=/path/to/wp >/dev/null 2>&1`.
- `/resume.pdf` must be served by WordPress (it is the `resume_url` setting, not a static file), so the web server config must not short-circuit `.pdf` requests with a static `try_files` rule.
- The server's PHP GD extension must have WebP support so WordPress can generate the WebP sub-sizes the theme references.

License: GPL-2.0-or-later.
