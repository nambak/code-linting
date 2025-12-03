<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->testDir = app_path('TestQueries');
    File::makeDirectory($this->testDir);
});

afterEach(function () {
    File::deleteDirectory($this->testDir);
});

test('command returns success when no anti-patterns found', function () {
    File::put($this->testDir.'/CleanCode.php', <<<'PHP'
<?php
namespace App\TestQueries;

class CleanCode {
    public function getUsers() {
        return User::query()->where('active', true)->paginate(15);
    }
}
PHP);

    $this->artisan('check:queries')
        ->assertSuccessful()
        ->expectsOutput('🔍 Checking for query anti-patterns...')
        ->expectsOutput('✅ No query anti-patterns detected!');
});

test('command detects Model::all() without pagination', function () {
    File::put($this->testDir.'/BadCode.php', <<<'PHP'
<?php
namespace App\TestQueries;

class BadCode {
    public function getAll() {
        return User::all();
    }
}
PHP);

    $this->artisan('check:queries')
        ->assertFailed()
        ->expectsOutputToContain('🔴 HIGH Severity');
});

test('command detects DB::raw usage', function () {
    File::put($this->testDir.'/RawQuery.php', <<<'PHP'
<?php
namespace App\TestQueries;

use Illuminate\Support\Facades\DB;

class RawQuery {
    public function dangerous() {
        return DB::raw('SELECT * FROM users');
    }
}
PHP);

    $this->artisan('check:queries')
        ->assertFailed()
        ->expectsOutputToContain('🔴 HIGH Severity')
        ->expectsOutputToContain('Using DB::raw()');
});

test('command detects get() without where clause', function () {
    File::put($this->testDir.'/GetWithoutWhere.php', <<<'PHP'
<?php
namespace App\TestQueries;

class GetWithoutWhere {
    public function getAll() {
        return User::query()->get();
    }
}
PHP);

    $this->artisan('check:queries')
        ->expectsOutputToContain('MEDIUM Severity');
});

test('command scans all PHP files in app directory', function () {
    File::put($this->testDir.'/ValidCode.php', <<<'PHP'
<?php
namespace App\TestQueries;

class ValidCode {
    public function getAll() {
        return User::query()->where('active', true)->paginate();
    }
}
PHP);

    $this->artisan('check:queries')
        ->assertSuccessful();
});

test('command groups issues by severity', function () {
    File::put($this->testDir.'/MultipleIssues.php', <<<'PHP'
<?php
namespace App\TestQueries;

use Illuminate\Support\Facades\DB;

class MultipleIssues {
    public function high() {
        return User::all();
    }

    public function alsoHigh() {
        return DB::raw('SELECT * FROM users');
    }

    public function medium() {
        return User::query()->get();
    }
}
PHP);

    $this->artisan('check:queries')
        ->assertFailed()
        ->expectsOutputToContain('🔴 HIGH Severity (2)')
        ->expectsOutputToContain('🟡 MEDIUM Severity');
});

test('command ignores patterns in string literals', function () {
    File::put($this->testDir.'/StringLiterals.php', <<<'PHP'
<?php
namespace App\TestQueries;

class StringLiterals {
    public function safe() {
        $comment = "Don't use User::all() in production";
        return User::query()->where('active', true)->paginate();
    }
}
PHP);

    $this->artisan('check:queries')
        ->assertSuccessful();
});

test('command returns correct exit code for high severity issues', function () {
    File::put($this->testDir.'/HighSeverity.php', <<<'PHP'
<?php
namespace App\TestQueries;

class HighSeverity {
    public function bad() {
        return User::all();
    }
}
PHP);

    $this->artisan('check:queries')
        ->assertExitCode(1);
});

test('command returns success exit code for only low/medium severity', function () {
    File::put($this->testDir.'/MediumSeverity.php', <<<'PHP'
<?php
namespace App\TestQueries;

class MediumSeverity {
    public function notTerrible() {
        return User::query()->get();
    }
}
PHP);

    $this->artisan('check:queries')
        ->assertExitCode(0);
});
