import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import { runInNewContext } from 'node:vm';

test('mobile navigation tracks tall sections in both scroll directions and after resizing', () => {
    const source = readFileSync(new URL('../resources/js/app.js', import.meta.url), 'utf8');
    const navigation = source.slice(source.indexOf('const landingPage ='), source.indexOf('// Animasi masuk card'));
    let scrollY = 0;
    let headerHeight = 124;
    const listeners = {};
    const frames = [];
    const sections = [['top', 150], ['panduan', 950], ['fasilitas', 2200]].map(([id, top]) => ({
        id,
        getBoundingClientRect: () => ({ top: top - scrollY }),
    }));
    const links = [...sections, ...sections].map(({ id }) => ({
        dataset: { landingNav: id, navActive: 'active', navInactive: 'inactive' },
        setAttribute(name, value) { this[name] = value; },
        removeAttribute(name) { delete this[name]; },
        addEventListener() {},
    }));
    const header = { getBoundingClientRect: () => ({ bottom: headerHeight + 8, height: headerHeight }) };
    const page = {
        querySelectorAll: (selector) => selector.includes('data-nav-active') ? links : sections,
        querySelector: () => header,
    };
    runInNewContext(navigation, {
        document: {
            querySelector: () => page,
            documentElement: { style: { setProperty() {} } },
        },
        window: {
            location: { hash: '' },
            addEventListener: (name, callback) => { listeners[name] = callback; },
        },
        requestAnimationFrame: (callback) => { frames.push(callback); return frames.length; },
        IntersectionObserver: class { observe() {} },
    });
    const activeSection = () => links.filter((link) => link['aria-current'] === 'page').map((link) => link.dataset.landingNav);
    const scrollTo = (position) => {
        scrollY = position;
        listeners.scroll?.();
        frames.splice(0).forEach((callback) => callback());
    };
    assert.deepEqual(activeSection(), ['top', 'top']);
    scrollTo(950 - (headerHeight + 20 + 16));
    assert.deepEqual(activeSection(), ['panduan', 'panduan']);
    scrollTo(950);
    assert.deepEqual(activeSection(), ['panduan', 'panduan']);
    scrollTo(1700);
    assert.deepEqual(activeSection(), ['panduan', 'panduan']);
    scrollTo(2200);
    assert.deepEqual(activeSection(), ['fasilitas', 'fasilitas']);
    scrollTo(1200);
    assert.deepEqual(activeSection(), ['panduan', 'panduan']);
    scrollTo(0);
    assert.deepEqual(activeSection(), ['top', 'top']);
    headerHeight = 72;
    listeners.resize();
    frames.splice(0).forEach((callback) => callback());
    scrollTo(950);
    assert.deepEqual(activeSection(), ['panduan', 'panduan']);
});

test('scroll reveals a card once without forcing layout', () => {
    const source = readFileSync(new URL('../resources/js/app.js', import.meta.url), 'utf8');
    const motion = source.slice(source.indexOf('const motionReducedMotion ='), source.indexOf("document.querySelectorAll('[data-facility-filters]')"));
    const classes = new Set();
    let callback;
    let unobserved = false;
    const card = {
        parentElement: null,
        matches: () => false,
        classList: {
            contains: (name) => classes.has(name),
            add: (...names) => names.forEach((name) => classes.add(name)),
            remove: (...names) => names.forEach((name) => classes.delete(name)),
        },
        style: { setProperty() {} },
        get offsetWidth() { throw new Error('reveal must not force layout'); },
    };
    runInNewContext(motion, {
        window: { matchMedia: () => ({ matches: false }), IntersectionObserver: true },
        document: {
            body: { classList: { add() {} } },
            querySelectorAll: (selector) => selector.includes('.auth-clay-card') ? [] : [card],
        },
        IntersectionObserver: class {
            constructor(handler) { callback = handler; }
            observe() {}
            unobserve(element) { assert.equal(element, card); unobserved = true; }
        },
    });
    callback([{ target: card, isIntersecting: false }]);
    assert.equal(classes.has('motion-pending'), true);
    callback([{ target: card, isIntersecting: true }]);
    assert.equal(classes.has('is-springed'), true);
    assert.equal(classes.has('motion-pending'), false);
    assert.equal(unobserved, true);
});
