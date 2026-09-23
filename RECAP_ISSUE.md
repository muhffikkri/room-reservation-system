# Issue: Rekap Okupansi & Kerusakan - Bottlenecks & Fallback Preparation

## 🔍 Bottleneck Analysis

### 1. **N+1 Query Problem** (Critical - `RecapService.php:46-77`)
```php
// Current: Runs separate query for EACH facility
$totalApproved = Reservation::approved()
    ->where('facility_id', $facility->id)
    ->where('start_time', '>=', $startDate)
    ->where('start_time', '<=', $endDate)
    ->get();
```
**Impact**: With 50 facilities = 50+ extra queries. Use eager loading with subqueries.

### 2. **PDF Export is Broken** (Critical - `RecapController.php:129-182`)
- Returns HTML with `Content-Type: application/pdf` → browsers display raw HTML
- No PDF library installed (dompdf, browsershot, wkhtmltopdf)
- **Fix needed**: Install `barryvdh/laravel-dompdf` or use headless Chrome

### 3. **No Caching** (Performance)
- Same date range requests hit DB every time
- No cache invalidation on reservation/report changes

### 5. **No Structured Logging** (Observability)
- No logging for: query duration, export generation, errors, user actions
- Missing context: user_id, date_range, facility_count, export_format

### 6. **Memory/Timeout Risk** (Scalability)
- Large date ranges load all reservations into memory
- No chunking for CSV/HTML generation
- No request timeout configuration

### 7. **Code Duplication** (Maintainability)
- Date validation repeated in all 6 controller methods
- Similar export logic duplicated

---

## 📋 Tasks

### Immediate (Before Merge)
- [ ] Fix N+1 query with eager loading + subqueries
- [ ] Add structured logging (query time, export metrics, errors)
- [ ] Extract date validation to FormRequest or helper
- [ ] Add caching with proper invalidation

### Short-term (Next Sprint)
- [ ] Install PDF library (`barryvdh/laravel-dompdf`)
- [ ] Implement real PDF generation with fallback to HTML download
- [ ] Add streaming CSV for large datasets
- [ ] Add request timeout handling

### Monitoring
- [ ] Add Prometheus metrics: `recap_query_duration_seconds`, `recap_export_duration_seconds`, `recap_export_total`
- [ ] Alert on query time > 2s or export time > 10s

---

## 🏷️ Labels
`performance`, `observability`, `technical-debt`, `pdf-export`, `recap`