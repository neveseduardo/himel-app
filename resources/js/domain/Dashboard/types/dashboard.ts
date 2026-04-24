import type { Period, PeriodCardBreakdown, PeriodSummary } from '@/domain/Period/types/period';

export interface StatusCounts {
	pending: number;
	paid: number;
	overdue: number;
}

export interface CategoryBreakdownItem {
	category_name: string;
	total: number;
}

export interface DashboardProps {
	period: Period | null;
	summary: PeriodSummary;
	cardBreakdown: PeriodCardBreakdown;
	periods: Period[];
	statusCounts: StatusCounts;
	categoryBreakdown: CategoryBreakdownItem[];
}
