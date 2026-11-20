/*
 * ISOLATED EXPERIMENT — do not merge into dev.
 * Attempts to read public LinkedIn job search results for a role keyword.
 *
 * Usage: npm run search -- "React Developer"
 * Output: results.json + results.html in this folder.
 */

const fs = require('fs');
const path = require('path');
const Nightmare = require('nightmare');

const keyword = process.argv[2] || 'React Developer';
const searchUrl = `https://www.linkedin.com/jobs/search/?keywords=${encodeURIComponent(keyword)}&location=Worldwide`;
const outputDir = __dirname;

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;');
}

function writeResults(payload) {
  fs.writeFileSync(path.join(outputDir, 'results.json'), JSON.stringify(payload, null, 2), 'utf8');

  const rows = payload.jobs.length
    ? payload.jobs.map((job) => `
        <tr>
          <td>${escapeHtml(job.title)}</td>
          <td>${escapeHtml(job.company)}</td>
          <td>${escapeHtml(job.location)}</td>
          <td>${escapeHtml(job.posted)}</td>
          <td>${job.url ? `<a href="${escapeHtml(job.url)}" target="_blank" rel="noreferrer">Open ↗</a>` : ''}</td>
        </tr>`).join('')
    : '<tr><td colspan="5">No job cards were extracted. Check results.json for the page snapshot.</td></tr>';

  const html = `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>LinkedIn experiment — ${escapeHtml(keyword)}</title>
  <style>
    body { font: 14px system-ui, sans-serif; margin: 2rem; background: #0b1120; color: #e2e8f0; }
    h1 { margin-bottom: .25rem; }
    .meta { color: #94a3b8; margin-bottom: 1.5rem; }
    table { width: 100%; border-collapse: collapse; }
    th, td { text-align: left; padding: .7rem; border-bottom: 1px solid #1e293b; }
    a { color: #818cf8; }
    .alert { padding: 1rem; background: #1e1b4b; border-radius: 8px; margin-bottom: 1rem; }
  </style>
</head>
<body>
  <h1>LinkedIn job search — experiment</h1>
  <p class="meta">Keyword: ${escapeHtml(keyword)} · ${escapeHtml(payload.searchedAt)} · Jobs found: ${payload.jobs.length}</p>
  ${payload.blocked ? '<p class="alert">LinkedIn showed a sign-in / bot check page. Results may be incomplete.</p>' : ''}
  <table>
    <thead><tr><th>Title</th><th>Company</th><th>Location</th><th>Posted</th><th>Link</th></tr></thead>
    <tbody>${rows}</tbody>
  </table>
</body>
</html>`;

  fs.writeFileSync(path.join(outputDir, 'results.html'), html, 'utf8');
}

(async () => {
  const payload = {
    keyword,
    searchedAt: new Date().toISOString(),
    url: searchUrl,
    blocked: false,
    pageTitle: null,
    pageTextSample: null,
    error: null,
    jobs: [],
  };

  const browser = Nightmare({
    show: false,
    waitTimeout: 30000,
    gotoTimeout: 30000,
  });

  try {
    const scraped = await browser
      .useragent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36')
      .goto(searchUrl)
      .wait(4000)
      .evaluate(() => {
        const pageText = document.body.innerText || '';
        const selectors = [
          '.base-search-card__title',
          '.job-search-card__title',
          '.jobs-search-results__list-item h3',
        ];

        const titleNodes = [];

        for (const selector of selectors) {
          titleNodes.push(...Array.from(document.querySelectorAll(selector)));
        }

        const seen = new Set();
        const jobs = [];

        for (const node of titleNodes) {
          const card = node.closest('li, .base-card, .job-search-card, .jobs-search-results__list-item') || node.parentElement;
          if (!card || seen.has(card)) {
            continue;
          }
          seen.add(card);

          const linkNode = card.querySelector('a[href*="/jobs/view/"]') || node.closest('a');
          const title = (node.textContent || '').trim();
          if (!title) {
            continue;
          }

          const cleanLine = (text) => {
            const lines = (text || '').split('\n').map((line) => line.trim()).filter(Boolean);
            return lines.length ? lines[0] : null;
          };

          const companyNode = card.querySelector(
            '.base-search-card__subtitle, .job-search-card__company-name, .hidden-nested-link',
          );
          const locationNode = card.querySelector(
            '.job-search-card__location, .base-search-card__metadata',
          );
          const cardText = card.innerText || '';
          const postedMatch = cardText.match(/\b(\d+)\s+(day|week|month|year)s?\s+ago\b/i);

          jobs.push({
            title,
            company: companyNode ? cleanLine(companyNode.textContent) : null,
            location: locationNode ? cleanLine(locationNode.textContent) : null,
            posted: postedMatch ? postedMatch[0] : null,
            url: linkNode ? new URL(linkNode.getAttribute('href'), window.location.href).href : null,
          });
        }

        return {
          pageTitle: document.title,
          pageTextSample: pageText.slice(0, 800),
          blocked: /authwall|sign in|join now|verify you are a human/i.test(pageText.slice(0, 2000)),
          jobs: jobs.slice(0, 25),
        };
      });

    payload.pageTitle = scraped.pageTitle;
    payload.pageTextSample = scraped.pageTextSample;
    payload.blocked = scraped.blocked;
    payload.jobs = scraped.jobs;
  } catch (error) {
    payload.error = error && error.message ? error.message : String(error);
  } finally {
    await browser.end().catch(() => undefined);
  }

  writeResults(payload);

  console.log('Keyword:', keyword);
  console.log('Page title:', payload.pageTitle || '(none)');
  console.log('Blocked / sign-in wall:', payload.blocked);
  console.log('Jobs extracted:', payload.jobs.length);
  console.log('Error:', payload.error || 'none');
  console.log('Wrote:', path.join(outputDir, 'results.json'));
  console.log('Wrote:', path.join(outputDir, 'results.html'));
})();
