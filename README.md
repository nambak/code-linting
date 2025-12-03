# Laravel Code Quality Starter Kit

![Code Quality](https://github.com/nambak/code-linting/actions/workflows/code-quality.yml/badge.svg)
![PHP Version](https://img.shields.io/badge/PHP-8.3-blue)
![Laravel](https://img.shields.io/badge/Laravel-12.x-red)
![PHPStan Level 5](https://img.shields.io/badge/PHPStan-level%205-brightgreen)
![Psalm Level 5](https://img.shields.io/badge/Psalm-level%205-blueviolet)
![Code Style](https://img.shields.io/badge/code%20style-pint-orange)
![Security](https://img.shields.io/badge/security-composer%20audit-green)
![Test Coverage](https://img.shields.io/badge/coverage-80%25-brightgreen)
![Tests](https://img.shields.io/badge/tests-Pest%20v4-success)

> 📦 **Laravel 12 프로젝트를 위한 코드 품질 검증 스타터 킷**
> AI 생성 코드를 포함한 모든 코드의 품질, 보안, 성능을 자동으로 검증합니다.

## 🚀 빠른 시작

### 이 템플릿으로 새 프로젝트 시작하기

1. **GitHub에서 "Use this template" 클릭**
2. 저장소 이름 입력하고 생성
3. 클론 후 설정:

```bash
# 프로젝트 클론
git clone https://github.com/your-username/your-project.git
cd your-project

# 초기 설정 (의존성 설치, .env 생성, 키 생성, 마이그레이션, 에셋 빌드)
composer setup

# 개발 서버 시작 (서버 + 큐 + 로그 + Vite)
composer run dev
```

### 기존 Laravel 프로젝트에 적용하기

```bash
# 1. 필요한 패키지 설치
composer require --dev larastan/larastan phpmd/phpmd beyondcode/laravel-query-detector
composer require --dev laravel/telescope barryvdh/laravel-ide-helper

# 2. 설정 파일 복사
# 다음 파일들을 프로젝트에 복사:
# - phpstan.neon
# - phpstan-bootstrap.php
# - phpmd.xml
# - pint.json
# - .github/workflows/code-quality.yml

# 3. composer.json의 scripts 섹션 복사

# 4. 코드 품질 검사 실행
composer check-all
```

## 📋 포함된 설정

| 파일 | 설명 |
|------|------|
| `phpstan.neon` | PHPStan Level 5 설정 + Larastan + IDE Helper |
| `phpstan-bootstrap.php` | PHPStan 부트스트랩 (autoload) |
| `phpmd.xml` | PHPMD 규칙 (Laravel 최적화) |
| `pint.json` | Laravel Pint 코드 스타일 설정 |
| `.github/workflows/code-quality.yml` | GitHub Actions CI/CD 워크플로우 |
| `app/Console/Commands/CheckQueryPatterns.php` | 쿼리 안티패턴 감지 명령어 |

## 🔍 코드 품질 검증

AI 생성 코드를 포함하여 다음 도구들로 자동 검증합니다:

### 정적 분석 도구 (CI/CD 자동 실행)
코드를 실행하지 않고 분석하여 커밋 전에 문제를 조기 발견합니다.

| 도구 | 목적 | 감지 항목 | 실행 시점 |
|------|------|-----------|-----------|
| 🎨 Laravel Pint | 코드 스타일 통일 | PSR-12, Laravel 컨벤션 | 커밋 전 / CI/CD |
| 🔬 PHPStan Level 5 | 타입 안전성 검증 | 타입 오류, null 참조, 메서드 호출 오류 | 커밋 전 / CI/CD |
| 🔐 Psalm Taint Analysis | 보안 취약점 분석 | SQL Injection, XSS, Command Injection, Path Traversal | 커밋 전 / CI/CD |
| 📊 PHPMD | 코드 품질 분석 | 복잡도, 중복 코드, 네이밍, 사용하지 않는 코드 | 커밋 전 / CI/CD |
| ⚠️ Query Pattern Check | 쿼리 안티패턴 감지 | `Model::all()`, `DB::raw()`, `get()` 무분별 사용, `first()` null 체크 누락 | 커밋 전 / CI/CD |
| 🔒 Composer Audit | 의존성 취약점 검사 | 알려진 CVE, 보안 패치 필요 패키지 | 커밋 전 / CI/CD |

### 런타임 모니터링 도구 (개발 환경)
애플리케이션 실행 중에 문제를 감지하고 경고합니다.

| 도구 | 목적 | 감지 항목 | 실행 시점 |
|------|------|-----------|-----------|
| 🚫 preventLazyLoading | Lazy Loading 차단 | N+1 쿼리, 미리 로드하지 않은 관계 접근 | 개발 환경 실행 중 |
| 🔍 Query Detector | N+1 실시간 감지 | N+1 쿼리, 중복 쿼리 패턴 | 개발 환경 실행 중 |
| 🔭 Laravel Telescope | 쿼리 시각화 및 분석 | 모든 쿼리, 느린 쿼리, N+1, 요청/응답, 로그 | 개발 환경 실행 중 |
| 📋 Telescope Job Watcher | 큐 작업 모니터링 | 큐 작업 실행 내역, 실패한 작업, 실행 시간, 페이로드 | 개발 환경 실행 중 |

### N+1 쿼리 방지 3중 방어선
1. **정적 분석** (`check:queries`) - 코드 작성 시점에 패턴으로 조기 발견
2. **런타임 차단** (`preventLazyLoading`) - 실행 중 Lazy Loading 시도 시 예외 발생
3. **실시간 모니터링** (`Query Detector`, `Telescope`) - 발생한 N+1 쿼리 감지 및 시각화

### 로컬에서 실행
```bash
# 전체 검사
composer check-all

# 개별 실행
composer pint-test       # 코드 스타일
composer phpstan         # 타입 안전성
composer psalm:taint     # 보안 취약점 (Taint Analysis)
composer phpmd           # 코드 품질
composer check:queries   # 쿼리 패턴
composer audit           # 의존성 취약점

# 테스트
composer test                  # 유닛/기능 테스트 (Pest)
composer test:coverage         # 테스트 + 커버리지 (최소 80%)
composer test:coverage-html    # HTML 커버리지 리포트 생성

# 자동 수정
composer fix

# Telescope 대시보드
php artisan serve
# http://localhost:8000/telescope
```

## 📋 큐 모니터링

이 프로젝트는 **Laravel Telescope**를 사용하여 큐 작업을 모니터링합니다.

### Telescope를 통한 큐 모니터링

Telescope의 **Job Watcher**와 **Batch Watcher**가 활성화되어 있어 다음 정보를 확인할 수 있습니다:

| 모니터링 항목 | 설명 |
|--------------|------|
| 🔄 실행 중인 작업 | 현재 처리 중인 큐 작업 |
| ✅ 완료된 작업 | 성공적으로 완료된 작업 내역 |
| ❌ 실패한 작업 | 실패한 작업과 예외 정보 |
| ⏱️ 실행 시간 | 각 작업의 처리 시간 |
| 📦 페이로드 | 작업에 전달된 데이터 |
| 🏷️ 배치 작업 | 배치 작업의 진행 상황과 결과 |

### 큐 모니터링 명령어

```bash
# Telescope 대시보드에서 큐 작업 확인
php artisan serve
# http://localhost:8000/telescope/jobs

# 실패한 큐 작업 목록 확인
php artisan queue:failed

# 특정 실패한 작업 재시도
php artisan queue:retry {job-id}

# 모든 실패한 작업 재시도
php artisan queue:retry all

# 실패한 작업 삭제
php artisan queue:forget {job-id}

# 모든 실패한 작업 삭제
php artisan queue:flush

# 최근 48시간 이내 실패한 작업만 삭제
php artisan queue:flush --hours=48

# 큐 워커 실행 (개발 환경)
php artisan queue:work --tries=3 --timeout=60

# 큐 워커 실행 (프로덕션 환경)
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

### 큐 작업 실패 알림 설정

실패한 큐 작업에 대한 알림을 받으려면 `AppServiceProvider`에서 설정할 수 있습니다:

```php
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;

Queue::failing(function (JobFailed $event) {
    // 실패 알림 로직
    Log::error('Queue job failed', [
        'connection' => $event->connectionName,
        'job' => $event->job->resolveName(),
        'exception' => $event->exception->getMessage(),
    ]);
});
```

## 🧪 테스트

이 프로젝트는 **Pest v4**를 사용하여 테스트합니다:

- **유닛 테스트**: 개별 클래스/메서드 단위 테스트
- **기능 테스트**: 전체 기능 흐름 테스트 (인증, 설정, 대시보드 등)
- **코드 커버리지**: 최소 80% 이상 유지 (CI/CD에서 검증)

### 테스트 실행 방법

```bash
# 모든 테스트 실행 (로컬 개발)
php artisan test
# 또는
composer test

# 커버리지와 함께 실행 (CI/CD 또는 PCOV/Xdebug 설치 시)
composer test:coverage

# HTML 리포트 생성 후 브라우저에서 확인
composer test:coverage-html
open coverage/index.html

# 특정 테스트만 실행
php artisan test --filter=testName
php artisan test tests/Feature/Auth/AuthenticationTest.php
```

### 코드 커버리지 설정 (선택사항)

로컬 개발 환경에서 코드 커버리지를 측정하려면 PCOV 또는 Xdebug가 필요합니다.

**Laravel Herd 사용자:**
1. Herd 앱 열기
2. Settings > PHP Extensions
3. PCOV 또는 Xdebug 활성화

**Homebrew PHP 사용자:**
```bash
pecl install pcov
# 또는
pecl install xdebug
```

**주의:** CI/CD 환경에서는 PCOV가 자동으로 설치되어 커버리지를 측정합니다.