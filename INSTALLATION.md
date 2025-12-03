# 설치 가이드

이 문서는 기존 Laravel 프로젝트에 코드 품질 검증 도구를 적용하는 상세한 가이드입니다.

## 📦 1. 패키지 설치

```bash
# 정적 분석 도구
composer require --dev larastan/larastan:^3.8
composer require --dev phpmd/phpmd:^2.15
composer require --dev vimeo/psalm:^6.13

# 개발 도구
composer require --dev beyondcode/laravel-query-detector:^2.1
composer require --dev laravel/telescope:^5.15
composer require --dev barryvdh/laravel-ide-helper:^3.6

# 테스트 도구 (이미 있다면 스킵)
composer require --dev pestphp/pest:^4.1
composer require --dev pestphp/pest-plugin-laravel:^4.0
```

## 📋 2. 설정 파일 복사

다음 파일들을 프로젝트 루트에 복사하세요:

### 필수 파일

```bash
# PHPStan 설정
phpstan.neon
phpstan-bootstrap.php

# PHPMD 설정
phpmd.xml

# Laravel Pint 설정 (선택사항, 기본 설정으로도 충분)
pint.json
```

### GitHub Actions 워크플로우

```bash
# CI/CD 파이프라인
.github/workflows/code-quality.yml
```

## ⚙️ 3. Composer Scripts 추가

`composer.json`의 `scripts` 섹션에 다음을 추가하세요:

```json
{
  "scripts": {
    "pint": "pint",
    "pint-test": "pint --test",
    "phpstan": "phpstan analyse --memory-limit=2G",
    "phpmd": "phpmd app,config,database,routes text phpmd.xml --exclude app/Providers/Filament",
    "check:queries": "php artisan check:queries",
    "psalm": "psalm --no-progress",
    "psalm:taint": "psalm --taint-analysis --no-progress",
    "audit": "composer audit --no-dev",
    "fix": "pint",
    "check-code": [
      "Composer\\Config::disableProcessTimeout",
      "@pint-test",
      "@phpstan",
      "@phpmd",
      "@check:queries"
    ],
    "check-all": [
      "Composer\\Config::disableProcessTimeout",
      "@pint-test",
      "@phpstan",
      "@psalm:taint",
      "@phpmd",
      "@check:queries",
      "@audit"
    ]
  }
}
```

## 🛠️ 4. Artisan 명령어 추가

쿼리 패턴 검사 명령어를 추가하세요:

```bash
# 명령어 파일 생성
php artisan make:command CheckQueryPatterns
```

`app/Console/Commands/CheckQueryPatterns.php` 내용을 템플릿에서 복사하세요.

## 🔧 5. Laravel Telescope 설정

```bash
# Telescope 설치 및 설정
php artisan telescope:install
php artisan migrate

# config/telescope.php에서 로컬 환경만 활성화 확인
# 'enabled' => env('TELESCOPE_ENABLED', true),
```

`app/Providers/AppServiceProvider.php`에 다음 추가:

```php
public function register(): void
{
    if ($this->app->environment('local')) {
        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);
    }
}
```

## 🔍 6. Query Detector 설정

`config/querydetector.php`를 publish하고 설정:

```bash
php artisan vendor:publish --provider="BeyondCode\QueryDetector\QueryDetectorServiceProvider"
```

## 🚫 7. Lazy Loading 방지 설정

`app/Providers/AppServiceProvider.php`의 `boot()` 메서드에 추가:

```php
public function boot(): void
{
    if (! $this->app->isProduction()) {
        Model::preventLazyLoading();
    }
}
```

## 📝 8. IDE Helper 설정

`.gitignore`에 추가:

```gitignore
_ide_helper.php
_ide_helper_models.php
.phpstorm.meta.php
```

IDE Helper 생성:

```bash
php artisan ide-helper:generate
php artisan ide-helper:models --nowrite
php artisan ide-helper:meta
```

## ✅ 9. 설치 확인

모든 도구가 정상 작동하는지 확인:

```bash
# 코드 스타일 검사
composer pint-test

# PHPStan 실행
composer phpstan

# PHPMD 실행
composer phpmd

# Psalm 실행
composer psalm:taint

# 쿼리 패턴 검사
composer check:queries

# 전체 검사
composer check-all
```

## 🎯 10. GitHub Actions 설정

저장소 설정에서 Actions 활성화:

1. GitHub 저장소 > Settings > Actions > General
2. "Allow all actions and reusable workflows" 선택
3. 코드를 푸시하면 자동으로 워크플로우 실행

## 🔄 11. 선택사항: 프리 커밋 훅

Git pre-commit hook을 추가하여 커밋 전 자동 검사:

```bash
# .git/hooks/pre-commit 생성
#!/bin/sh

echo "Running code quality checks..."

# 코드 스타일 자동 수정
composer fix

# 정적 분석
composer check-code

if [ $? -ne 0 ]; then
    echo "❌ Code quality checks failed. Please fix the issues before committing."
    exit 1
fi

echo "✅ All checks passed!"
```

실행 권한 부여:

```bash
chmod +x .git/hooks/pre-commit
```

## 📚 추가 참고자료

- [PHPStan 문서](https://phpstan.org/)
- [PHPMD 문서](https://phpmd.org/)
- [Laravel Pint 문서](https://laravel.com/docs/pint)
- [Psalm 문서](https://psalm.dev/)
- [Larastan 문서](https://github.com/larastan/larastan)
