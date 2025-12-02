# code-liting

![Code Quality](https://github.com/사용자명/저장소명/actions/workflows/code-quality.yml/badge.svg)
![PHP Version](https://img.shields.io/badge/PHP-8.2-blue)
![Laravel](https://img.shields.io/badge/Laravel-11.x-red)

## 🔍 코드 품질 검증

AI 생성 코드를 포함하여 다음 도구들로 자동 검증합니다:

| 도구 | 목적 | 설정 |
|------|------|------|
| 🎨 Laravel Pint | 코드 스타일 | Laravel preset |
| 🔬 PHPStan | 정적 분석 | Level 5 |
| 📊 PHPMD | 코드 품질 | Clean Code, Code Size |
| 🔭 Laravel Telescope | 쿼리 모니터링 | N+1, 느린 쿼리 감지 |
| 🔍 Query Detector | N+1 실시간 감지 | 개발 환경 |
| 🚫 preventLazyLoading | Lazy Loading 차단 | 개발 환경 |

### 로컬에서 실행
```bash
# 전체 검사
composer check-all

# 개별 실행
composer pint-test     # 코드 스타일
composer phpstan       # 정적 분석
composer phpmd         # 코드 품질
composer check:queries # 쿼리 패턴

# 자동 수정
composer fix

# Telescope 대시보드
php artisan serve
# http://localhost:8000/telescope
```