<?php

use Leaf\Llms\Sections;

test('new files get frontmatter and a marked section', function () {
    [$contents, $status] = Sections::merge(null, 'Hello', "name: x\ndescription: y");

    expect($status)->toBe(Sections::CREATED);
    expect($contents)->toBe("---\nname: x\ndescription: y\n---\n\n" . Sections::START . "\nHello\n" . Sections::END . "\n");
});

test('only the marked section is replaced', function () {
    $existing = "Mine above\n\n" . Sections::START . "\nold\n" . Sections::END . "\n\nMine below\n";
    [$contents, $status] = Sections::merge($existing, 'new');

    expect($status)->toBe(Sections::UPDATED);
    expect($contents)->toBe("Mine above\n\n" . Sections::START . "\nnew\n" . Sections::END . "\n\nMine below\n");
});

test('identical output is reported as unchanged', function () {
    [$first] = Sections::merge(null, 'same');
    [$second, $status] = Sections::merge($first, 'same');

    expect($status)->toBe(Sections::UNCHANGED);
    expect($second)->toBe($first);
});

test('files without markers keep their text and get the section appended', function () {
    [$contents, $status] = Sections::merge("# Notes\n\nKeep me.\n", 'generated');

    expect($status)->toBe(Sections::APPENDED);
    expect($contents)->toStartWith("# Notes\n\nKeep me.\n\n" . Sections::START);
});
