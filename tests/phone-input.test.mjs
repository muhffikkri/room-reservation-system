import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';

for (const path of ['auth/register.blade.php', 'components/admin/account-form.blade.php']) {
    test(`${path} validates phone input with the HTML unicode sets flag`, () => {
        const source = readFileSync(new URL(`../resources/views/${path}`, import.meta.url), 'utf8');
        assert.match(source, /pattern="{{ \\App\\Support\\AccountAttributes::PHONE_INPUT_PATTERN }}"/);
        const attributes = readFileSync(new URL('../app/Support/AccountAttributes.php', import.meta.url), 'utf8');
        const pattern = attributes.match(/PHONE_INPUT_PATTERN = '([^']+)'/)[1];
        const expression = new RegExp(`^(?:${pattern})$`, 'v');

        for (const value of ['081234567890', '6281234567890', '+62 812-3456-7890', '+62 (812) 3456.7890']) {
            assert.equal(expression.test(value), true, value);
        }
        for (const value of ['not-a-phone', '0812abc56789', '123', '1'.repeat(21)]) {
            assert.equal(expression.test(value), false, value);
        }
    });
}
