# Implementation Plan: Period Dashboard

## Overview

Implementação incremental do dashboard de períodos financeiros. Começa com a instalação de dependências de gráficos, segue com backend (service + controller + rota), depois frontend (tipos, sidebar, página, gráficos), e finaliza com testes (PHPUnit, E2E, property-based). Cada etapa constrói sobre a anterior, sem código órfão.

## Tasks

- [x] 1. Install chart dependencies
  - Run `npx shadcn-vue@latest add chart-donut chart-bar` to install DonutChart and BarChart components with @unovis/vue and @unovis/ts dependencies
  - Add Unovis CSS variables to `resources/css/app.css` inside a `@layer base` block
  - Verify the generated components exist in `resources/js/domain/Shared/components/ui/chart-donut/` and `resources/js/domain/Shared/components/ui/chart-bar/`
  - _Requirements: 11.1, 11.2, 11.3_

- [x] 2. Backend: DashboardService and DashboardPageController
  - [x] 2.1 Create DashboardServiceInterface and DashboardService
    - Create `app/Domain/Dashboard/Contracts/DashboardServiceInterface.php` with `getStatusCountsForPeriod(string $periodUid, string $userUid): array` and `getCategoryBreakdownForPeriod(string $periodUid, string $userUid): array`
    - Create `app/Domain/Dashboard/Services/DashboardService.php` implementing the interface
    - `getStatusCountsForPeriod` queries transactions grouped by status (PENDING, PAID, OVERDUE) for the given period and user
    - `getCategoryBreakdownForPeriod` queries OUTFLOW transactions joined with categories, grouped by category name, summing amounts, ordered by total DESC
    - Register `DashboardServiceInterface` → `DashboardService` binding in `AppServiceProvider.php`
    - _Requirements: 2.5, 2.6_

  - [x] 2.2 Create DashboardPageController and route
    - Create `app/Domain/Dashboard/Controllers/DashboardPageController.php` as an invokable controller
    - Inject `PeriodServiceInterface` and `DashboardServiceInterface`
    - Resolve period: query param `period` > current period > null
    - Build summary, cardBreakdown, statusCounts, categoryBreakdown (zeroed if no period)
    - Return `Inertia::render('Dashboard', [...])` with all 6 props: period, summary, cardBreakdown, periods, statusCounts, categoryBreakdown
    - Create `app/Domain/Dashboard/Routes/web.php` with `Route::get('dashboard', DashboardPageController::class)->name('dashboard')`
    - Replace `Route::inertia('dashboard', 'Dashboard')` in `routes/web.php` with `require base_path('app/Domain/Dashboard/Routes/web.php')`
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8_

- [x] 3. Frontend: TypeScript types
  - Create `resources/js/domain/Dashboard/types/dashboard.ts` with `StatusCounts`, `CategoryBreakdownItem`, and `DashboardProps` interfaces
  - Import `Period`, `PeriodCardBreakdown`, `PeriodSummary` from `@/domain/Period/types/period`
  - _Requirements: 2.5, 2.6_

- [x] 4. Frontend: Sidebar update
  - In `AppSidebar.vue`, add `BarChart3` import from `lucide-vue-next`
  - Add `{ title: 'Dashboard', href: '/dashboard', icon: BarChart3 }` as the first item in `financeNavItems`
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [x] 5. Frontend: Dashboard.vue page with PeriodSelector and summary cards
  - [x] 5.1 Create PeriodSelector component
    - Create `resources/js/domain/Dashboard/components/PeriodSelector.vue`
    - Use shadcn-vue `Select`, `SelectTrigger`, `SelectContent`, `SelectItem`
    - Props: `periods: Period[]`, `selectedUid: string | null`
    - Emits: `update:selectedUid`
    - Format each period as "Mês Ano" using Portuguese month names
    - _Requirements: 4.1, 4.2, 4.4, 4.5_

  - [x] 5.2 Implement Dashboard.vue page
    - Replace current `resources/js/pages/Dashboard.vue` with full dashboard implementation
    - Use `defineProps<DashboardProps>()` to receive Inertia props
    - Render PeriodSelector, 4 summary cards (Entradas, Saídas, Saldo, Total Cartões) using shadcn-vue Card
    - Format values with `formatCurrency` from `@/domain/Shared/services/format`
    - Apply green color for positive balance, red for negative
    - Handle empty state when `period` is null (message to create a period)
    - On period change, call `router.get('/dashboard', { period: uid }, { preserveState: false })`
    - Responsive grid: cards `grid-cols-1 md:grid-cols-2 xl:grid-cols-4`, charts `grid-cols-1 lg:grid-cols-2`
    - Add `data-testid` attributes on chart containers for E2E testing
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 4.1, 4.3, 4.6, 10.1, 10.2, 10.3, 10.4, 10.5_

- [x] 6. Checkpoint — Verify backend + page render
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 7. Frontend: Chart components
  - [x] 7.1 Create OutflowCompositionChart component
    - Create `resources/js/domain/Dashboard/components/OutflowCompositionChart.vue`
    - DonutChart showing 4 outflow sources: Despesas Fixas, Parcelas de Cartão, Manuais, Transferências
    - Props derived from `PeriodSummary` (total_fixed_expenses, total_credit_card_installments, total_manual, total_transfer)
    - Empty state: "Sem saídas neste período" when all values are zero
    - Use `formatCurrency` for value formatting
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

  - [x] 7.2 Create InflowVsOutflowChart component
    - Create `resources/js/domain/Dashboard/components/InflowVsOutflowChart.vue`
    - Grouped BarChart comparing total_inflow and total_outflow
    - Green for Entradas, red for Saídas
    - Tooltip with R$ formatted values
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

  - [x] 7.3 Create CardBreakdownChart component
    - Create `resources/js/domain/Dashboard/components/CardBreakdownChart.vue`
    - Horizontal BarChart showing total per credit card
    - Props: `PeriodCardBreakdown`
    - Empty state: "Sem dados de cartão"
    - _Requirements: 7.1, 7.2, 7.3, 7.4_

  - [x] 7.4 Create StatusChart component
    - Create `resources/js/domain/Dashboard/components/StatusChart.vue`
    - DonutChart showing transaction counts by status (PENDING, PAID, OVERDUE)
    - Colors: yellow for PENDING, green for PAID, red for OVERDUE
    - Display total transaction count in center
    - Empty state: "Sem transações"
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

  - [x] 7.5 Create CategoryBreakdownChart component
    - Create `resources/js/domain/Dashboard/components/CategoryBreakdownChart.vue`
    - Horizontal BarChart showing OUTFLOW totals by category, sorted by value DESC
    - Props: `CategoryBreakdownItem[]`
    - Empty state: "Sem dados"
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [x] 8. Checkpoint — Verify full page with charts
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 9. Backend: PHPUnit tests
  - [x] 9.1 Create DashboardPageControllerTest
    - Create `tests/Feature/DashboardPageControllerTest.php`
    - Test: returns Inertia response with all expected props (period, summary, cardBreakdown, periods, statusCounts, categoryBreakdown)
    - Test: respects `?period=uid` query param to select a specific period
    - Test: returns empty/zeroed data when user has no periods
    - Test: returns correct data for period with mixed transactions (INFLOW/OUTFLOW, multiple statuses and sources)
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8_

  - [x] 9.2 Create DashboardServiceTest
    - Create `tests/Feature/DashboardServiceTest.php`
    - Test: `getStatusCountsForPeriod` returns correct counts per status (PENDING, PAID, OVERDUE)
    - Test: `getStatusCountsForPeriod` returns zeroed counts for period with no transactions
    - Test: `getCategoryBreakdownForPeriod` returns categories sorted by total DESC
    - Test: `getCategoryBreakdownForPeriod` returns empty array for period with no OUTFLOW transactions
    - _Requirements: 2.5, 2.6_

- [ ] 10. E2E: Seeder updates and Playwright tests
  - [ ] 10.1 Update E2eTestSeeder for dashboard data
    - Add PAID and OVERDUE transactions to Janeiro 2025 period (currently only PENDING)
    - Add OUTFLOW transactions with varied categories (Alimentação, Transporte, Saúde) to Janeiro 2025
    - Ensure Março 2025 remains without transactions (for empty state testing)
    - _Requirements: 12.10_

  - [ ] 10.2 Create DashboardPage Page Object
    - Create `e2e/pages/DashboardPage.ts` with methods: `goto()`, `getPageTitle()`, `getSummaryCardValue(label)`, `getSelectedPeriod()`, `selectPeriod(label)`, `isChartVisible(testId)`, `getEmptyStateMessage(testId)`, `getSidebarItems()`
    - Follow existing Page Object pattern from `PeriodPage.ts`
    - _Requirements: 12.9_

  - [ ] 10.3 Create dashboard E2E test specs
    - Create `e2e/tests/dashboard.spec.ts`
    - Organize by `test.describe`: Page Load, Summary Cards, Period Selector, Charts, Empty States, Responsiveness, Sidebar
    - Test page loads with correct title and summary cards visible
    - Test summary card values formatted in R$
    - Test period selector shows current period as default and updates data on change
    - Test all 5 charts are rendered with data (via `data-testid`)
    - Test empty states when period has no data (Março 2025)
    - Test responsive layout at 375px (mobile) and 1280px (desktop)
    - Test Dashboard is first sidebar item in "Financeiro" group
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5, 12.6, 12.7, 12.8_

- [ ] 11. Property-based tests
  - [ ]* 11.1 Write property test for period formatting
    - **Property 1: Period formatting produces correct month name and year**
    - Create test in `tests/Feature/DashboardPropertyTest.php`
    - Generate random month (1–12) and year via `random_int`, 100 iterations
    - Verify `formatPeriodLabel(month, year)` output contains the correct Portuguese month name and the year as substring
    - Tag: `Feature: period-dashboard, Property 1: Period formatting produces correct month name and year`
    - **Validates: Requirements 4.5**

  - [ ]* 11.2 Write property test for category breakdown sort order
    - **Property 2: Category breakdown is sorted by value descending**
    - Create test in `tests/Feature/DashboardPropertyTest.php`
    - Generate random arrays of category/total pairs, insert into DB, call `getCategoryBreakdownForPeriod`, verify descending order
    - 100 iterations using `random_int` for amounts
    - Tag: `Feature: period-dashboard, Property 2: Category breakdown is sorted by value descending`
    - **Validates: Requirements 9.2**

- [ ] 12. Final checkpoint — Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- Chart components use shadcn-vue Chart wrappers (DonutChart, BarChart) based on @unovis/vue
- The existing `formatCurrency` utility in `@/domain/Shared/services/format.ts` is reused for all R$ formatting
