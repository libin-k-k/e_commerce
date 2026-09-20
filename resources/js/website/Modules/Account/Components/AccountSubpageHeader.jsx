import { Link } from '@inertiajs/react';

export default function AccountSubpageHeader({ title, backHref = '/account' }) {
    return (
        <div className="account-subhead">
            <Link href={backHref} className="account-subhead__back" aria-label="Back to account">
                <BackIcon />
                <span>Account</span>
            </Link>
            <h1 className="account-subhead__title">{title}</h1>
        </div>
    );
}

function BackIcon() {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d="M15 6l-6 6 6 6"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}
