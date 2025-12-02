# code-linting

![Code Quality](https://github.com/nambak/code-linting/actions/workflows/code-quality.yml/badge.svg)
![PHP Version](https://img.shields.io/badge/PHP-8.3-blue)
![Laravel](https://img.shields.io/badge/Laravel-12.x-red)
![PHPStan Level 5](https://img.shields.io/badge/PHPStan-level%205-brightgreen)
![Psalm Level 5](https://img.shields.io/badge/Psalm-level%205-blueviolet)
![Code Style](https://img.shields.io/badge/code%20style-pint-orange)
![Security](https://img.shields.io/badge/security-composer%20audit-green)
![Test Coverage](https://img.shields.io/badge/coverage-80%25-brightgreen)
![Tests](https://img.shields.io/badge/tests-Pest%20v4-success)


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

## 🧪 테스트

이 프로젝트는 **Pest v4**를 사용하여 테스트합니다:

- **유닛 테스트**: 개별 클래스/메서드 단위 테스트
- **기능 테스트**: 전체 기능 흐름 테스트 (인증, 설정, 대시보드 등)
- **코드 커버리지**: 최소 80% 이상 유지 (CI/CD에서 검증)

### 테스트 실행 방법

```bash
# 모든 테스트 실행
php artisan test
# 또는
composer test

# 커버리지와 함께 실행 (PCOV 또는 Xdebug 필요)
composer test:coverage

# HTML 리포트 생성 후 브라우저에서 확인
composer test:coverage-html
open coverage/index.html

# 특정 테스트만 실행
php artisan test --filter=testName
php artisan test tests/Feature/Auth/AuthenticationTest.php
```