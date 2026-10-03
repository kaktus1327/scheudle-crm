import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { UserMenuContent } from '@/components/user-menu-content';
import { useInitials } from '@/hooks/use-initials';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

export function UserMenu() {
    const { auth } = usePage<SharedData>().props;
    const getInitials = useInitials();

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <button type="button" className="hover:bg-muted flex items-center gap-2 rounded-md p-1 pr-2 text-sm">
                    <span className="bg-secondary text-secondary-foreground flex size-8 items-center justify-center rounded-md text-xs font-semibold">
                        {getInitials(auth.user.name)}
                    </span>
                    <span className="hidden font-medium sm:inline">{auth.user.name}</span>
                </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="w-56" align="end">
                <UserMenuContent user={auth.user} />
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
