import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

test('queue accepts server URLs, preserves filters and cancels superseded requests', async () => {
    const source = readFileSync(new URL('../resources/js/app.js', import.meta.url), 'utf8');
    const block = source.slice(source.indexOf('const loadingIndicator ='), source.indexOf('function getActionsHtml'));
    const nodes = Object.fromEntries(['loading-indicator', 'reservation-body', 'pagination-container'].map(id => [id, {
        innerHTML: '', classList: { add() {}, remove() {}, toggle() {} },
    }]));
    const requests = [];
    const history = [];
    let click;
    vm.runInNewContext(block, {
        URL, URLSearchParams, AbortController, console,
        window: { location: { origin: 'http://localhost:8000', href: 'http://localhost:8000/petugas/reservasi' } },
        document: { getElementById: id => nodes[id], querySelectorAll: () => [], addEventListener: (_, handler) => { click = handler; } },
        history: { pushState: (_, __, url) => history.push(url) },
        setActiveQueueTab() {},
        fetch: (url, options) => new Promise((resolve, reject) => {
            requests.push({ url, ...options, resolve });
            options.signal.addEventListener('abort', () => reject({ name: 'AbortError' }));
        }),
    });
    const navigate = url => click({ button: 0, target: { closest: () => ({ getAttribute: () => url }) }, preventDefault() {} });
    navigate('http://localhost:8000/petugas/reservasi?tab=selesai&page=2');
    navigate('http://localhost:8000/petugas/reservasi?tab=selesai&page=3');
    assert.equal(requests[0].signal.aborted, true);
    assert.equal(requests[1].url, '/petugas/reservasi/data?tab=selesai&page=3');
    requests[1].resolve({ json: async () => ({ reservations: [], pagination: { last_page: 1000 }, pagination_html: '<nav>bounded server pagination</nav>' }) });
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(nodes['pagination-container'].innerHTML, '<nav>bounded server pagination</nav>');
    assert.deepEqual(history, ['http://localhost:8000/petugas/reservasi?tab=selesai&page=3']);
    click({ button: 0, ctrlKey: true });
    assert.equal(requests.length, 2);
});
