import * as React from 'react';

import { cn } from '@/lib/utils';

const NativeSelect = React.forwardRef<HTMLSelectElement, React.ComponentProps<'select'>>(({ className, ...props }, ref) => (
    <select
        ref={ref}
        className={cn(
            'border-input bg-background focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 text-base focus-visible:ring-2 focus-visible:outline-hidden disabled:opacity-50 md:text-sm',
            className,
        )}
        {...props}
    />
));
NativeSelect.displayName = 'NativeSelect';

export { NativeSelect };
