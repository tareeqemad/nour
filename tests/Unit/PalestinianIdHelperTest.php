<?php

use App\Helpers\PalestinianIdHelper;

it('يقبل أرقام هوية صحيحة', function () {
    expect(PalestinianIdHelper::isValid('999999998'))->toBeTrue();
    expect(PalestinianIdHelper::isValid('300000007'))->toBeTrue();
    expect(PalestinianIdHelper::isValid('123456782'))->toBeTrue();
});

it('يرفض أرقام هوية غير صحيحة', function () {
    expect(PalestinianIdHelper::isValid('123456789'))->toBeFalse();
    expect(PalestinianIdHelper::isValid('111111111'))->toBeFalse();
    expect(PalestinianIdHelper::isValid('999999999'))->toBeFalse();
});

it('يرفض الأرقام التي لا تتكون من 9 خانات', function () {
    expect(PalestinianIdHelper::isValid('12345678'))->toBeFalse();
    expect(PalestinianIdHelper::isValid('1234567890'))->toBeFalse();
    expect(PalestinianIdHelper::isValid(''))->toBeFalse();
});

it('يرفض القيم غير الرقمية', function () {
    expect(PalestinianIdHelper::isValid('abcdefghi'))->toBeFalse();
    expect(PalestinianIdHelper::isValid('12345678a'))->toBeFalse();
    expect(PalestinianIdHelper::isValid('123-456-78'))->toBeFalse();
});

it('يتعامل مع القيم الفارغة', function () {
    expect(PalestinianIdHelper::isValid(null))->toBeFalse();
});

it('يقبل الرقم كعدد صحيح', function () {
    expect(PalestinianIdHelper::isValid(999999998))->toBeTrue();
});

it('يتجاهل الفراغات في البداية والنهاية', function () {
    expect(PalestinianIdHelper::isValid(' 999999998 '))->toBeTrue();
});
