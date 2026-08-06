import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { existsSync, mkdtempSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { delimiter, join } from 'node:path';
import { pathToFileURL } from 'node:url';
import test from 'node:test';

function findChromiumBrowser() {
    const executableNames = process.platform === 'win32' ? ['chrome.exe', 'msedge.exe'] : ['google-chrome', 'chromium', 'chromium-browser'];
    const pathCandidates = (process.env.PATH || '')
        .split(delimiter)
        .flatMap((directory) => executableNames.map((name) => join(directory, name)));
    const platformCandidates = process.platform === 'win32'
        ? [
              'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
              'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
              'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
              'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
          ]
        : [];

    return [...pathCandidates, ...platformCandidates].find(existsSync);
}

test('right-click target over a Summernote placeholder remains the editable area', (t) => {
    const browser = findChromiumBrowser();
    if (!browser) {
        t.skip('Chromium browser is required for hit-testing');
        return;
    }

    const blade = readFileSync(
        new URL('../../resources/views/partials/layout-app-styles.blade.php', import.meta.url),
        'utf8',
    );
    const appStyles = blade.match(/<style>([\s\S]*?)<\/style>/)?.[1];
    assert.ok(appStyles, 'expected shared application styles');

    const workDir = mkdtempSync(join(tmpdir(), 'summernote-placeholder-hit-test-'));
    const fixturePath = join(workDir, 'fixture.html');
    const profilePath = join(workDir, 'chrome-profile');
    const fixture = `<!doctype html>
        <style>
            .note-editing-area { position: relative; }
            .note-placeholder { position: absolute; display: block; color: gray; }
            .note-editable { width: 400px; height: 100px; }
            ${appStyles}
        </style>
        <div class="note-editor">
            <div class="note-editing-area">
                <div class="note-placeholder">Placeholder</div>
                <div class="note-editable" contenteditable="true"></div>
            </div>
        </div>
        <output id="hit-target"></output>
        <script>
            const placeholder = document.querySelector('.note-placeholder');
            const bounds = placeholder.getBoundingClientRect();
            document.querySelector('#hit-target').textContent = document
                .elementFromPoint(bounds.left + 1, bounds.top + 1)
                .className;
        </script>`;

    try {
        writeFileSync(fixturePath, fixture);
        const output = execFileSync(
            browser,
            [
                '--headless=new',
                '--disable-gpu',
                '--no-sandbox',
                `--user-data-dir=${profilePath}`,
                '--dump-dom',
                pathToFileURL(fixturePath).href,
            ],
            { encoding: 'utf8' },
        );

        assert.match(output, /<output id="hit-target">note-editable<\/output>/);
    } finally {
        rmSync(workDir, { recursive: true, force: true });
    }
});
