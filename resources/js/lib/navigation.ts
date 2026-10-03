import { type NavItem, type SharedData } from '@/types';
import {
    Archive,
    ArrowLeftRight,
    CalendarClock,
    CalendarDays,
    CalendarRange,
    FileBadge,
    LayoutDashboard,
    LayoutTemplate,
    Mail,
    UserRound,
    Users,
} from 'lucide-react';

export function navigationFor({ auth, badges }: Pick<SharedData, 'auth' | 'badges'>): NavItem[] {
    if (auth.user.role === 'manager') {
        return [
            { title: 'Pulpit', url: route('dashboard'), icon: LayoutDashboard, primary: true },
            { title: 'Grafiki', url: route('manager.schedules.index'), icon: CalendarDays, primary: true },
            { title: 'Wymiany', url: route('manager.trades.index'), icon: ArrowLeftRight, primary: true, badge: badges.trades },
            { title: 'Wiadomości', url: route('manager.messages.index'), icon: Mail, primary: true },
            { title: 'Pracownicy', url: route('manager.users.index'), icon: Users },
            { title: 'Dokumenty', url: route('manager.documents.index'), icon: FileBadge },
            { title: 'Szablony', url: route('manager.templates.index'), icon: LayoutTemplate },
            { title: 'Archiwum', url: route('archive.index'), icon: Archive },
        ];
    }

    return [
        { title: 'Pulpit', url: route('dashboard'), icon: LayoutDashboard, primary: true },
        { title: 'Grafik', url: route('schedule.index'), icon: CalendarDays, primary: true },
        { title: 'Giełda', url: route('trades.index'), icon: ArrowLeftRight, primary: true, badge: badges.trades },
        { title: 'Dyspo', url: route('availability.edit'), icon: CalendarClock, primary: true },
        { title: 'Moje zmiany', url: route('schedule.mine'), icon: CalendarRange },
        { title: 'Wiadomości', url: route('messages.index'), icon: Mail, badge: badges.messages },
        { title: 'Dokumenty', url: route('documents.index'), icon: FileBadge },
        { title: 'Archiwum', url: route('archive.index'), icon: Archive },
        { title: 'Profil', url: route('profile.edit'), icon: UserRound },
    ];
}

export function isActive(itemUrl: string, currentUrl: string): boolean {
    const path = new URL(itemUrl, window.location.origin).pathname;
    const current = currentUrl.split('?')[0];

    return current === path || (path !== '/dashboard' && current.startsWith(path + '/'));
}
