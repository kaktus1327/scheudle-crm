import { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="3" y="2" width="18" height="20" rx="2" stroke="currentColor" strokeWidth="2" />
            <path d="M7 2v20M17 2v20" stroke="currentColor" strokeWidth="2" />
            <path d="M3 7h4M3 12h4M3 17h4M17 7h4M17 12h4M17 17h4" stroke="currentColor" strokeWidth="2" />
        </svg>
    );
}
