import { LucideIcon } from 'lucide-react';

export type Role = 'manager' | 'employee';

export interface User {
    id: number;
    name: string;
    email: string;
    role: Role;
    avatar?: string;
    email_verified_at: string | null;
    must_change_password: boolean;
}

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    url: string;
    icon: LucideIcon;
    badge?: number;

    primary?: boolean;
}

export interface SharedData {
    name: string;
    auth: Auth;
    badges: { messages?: number; trades?: number };
    flash: { success: string | null; error: string | null };
    [key: string]: unknown;
}

export interface Position {
    id: number;
    name: string;
    slug: string;
}

export interface Person {
    id: number;
    name: string;
    positions?: Position[];
}

export interface WeekDay {
    date: string;
    weekday: string;
    isWeekend: boolean;
}

export interface CinemaWeek {
    start: string;
    end: string;
    label: string;
    days: WeekDay[];
}

export interface Shift {
    id: number;
    date: string;
    start_time: string;
    end_time: string;
    note: string | null;
    hours: number;
    position?: Position;
    user: Person | null;
    original_user: Person | null;
    is_open: boolean;
    was_traded: boolean;
}

export interface Schedule {
    id: number;
    week: CinemaWeek;
    status: 'draft' | 'published';
    status_label: string;
    published_at: string | null;
    template?: { id: number; name: string } | null;
    shifts?: Shift[];
    shifts_count?: number;
}

export type TradeType = 'swap' | 'giveaway' | 'open_offer' | 'open_shift_claim';
export type TradeStatus = 'pending_peer' | 'open' | 'pending_manager' | 'approved' | 'rejected' | 'cancelled';

export interface ShiftTrade {
    id: number;
    type: TradeType;
    type_label: string;
    status: TradeStatus;
    status_label: string;
    note: string | null;
    availability_conflict?: boolean;
    requester?: Person | null;
    target_user?: Person | null;
    claimer?: Person | null;
    offered_shift?: Shift;
    requested_shift?: Shift | null;
    created_at: string;
    can: { respond: boolean; claim: boolean; cancel: boolean; decide: boolean };
}

export interface MessageAttachment {
    id: number;
    name: string;
    size: number;
    url: string;
}

export interface Message {
    id: number;
    title: string;
    body: string;
    excerpt: string;
    requires_acknowledgement: boolean;
    author?: Person | null;
    attachments?: MessageAttachment[];
    read_at: string | null;
    acknowledged_at: string | null;
    recipients_count?: number;
    acknowledged_count?: number;
    created_at: string;
}

export interface UserDocument {
    id: number;
    type?: { id: number; name: string };
    user?: Person;
    valid_until: string;
    days_left: number;
    expiring_soon: boolean;
    note: string | null;
}

export interface Employee {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    role: Role;
    role_label: string;
    hourly_rate: number;
    must_change_password: boolean;
    deleted_at: string | null;
    positions?: Position[];
    documents?: UserDocument[];
}
