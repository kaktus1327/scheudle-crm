import { format, formatDistanceToNow, parseISO } from 'date-fns';
import { pl } from 'date-fns/locale';

const upperFirst = (text: string) => text.charAt(0).toUpperCase() + text.slice(1);

export const formatDate = (date: string, pattern = 'EEEE, d MMMM') => upperFirst(format(parseISO(date), pattern, { locale: pl }));

export const formatShortDate = (date: string) => upperFirst(format(parseISO(date), 'EEE d.MM', { locale: pl }));

export const formatRelative = (date: string) => formatDistanceToNow(parseISO(date), { locale: pl, addSuffix: true });

export const formatMoney = (value: number) =>
    new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'PLN', maximumFractionDigits: 2 }).format(value);

export const formatHours = (value: number) => `${new Intl.NumberFormat('pl-PL', { maximumFractionDigits: 1 }).format(value)} h`;

export const formatFileSize = (bytes: number) => (bytes > 1024 * 1024 ? `${(bytes / 1024 / 1024).toFixed(1)} MB` : `${Math.ceil(bytes / 1024)} KB`);
