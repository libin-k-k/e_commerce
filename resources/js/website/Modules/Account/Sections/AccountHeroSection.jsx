import { Link } from '@inertiajs/react';

export default function AccountHeroSection({ profile }) {
    const initials = (profile?.name ?? 'A')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');

    return (
        <section className="account-hero" aria-label="Account summary">
            <div className="account-hero__avatar" aria-hidden="true">
                {initials || 'A'}
            </div>
            <div className="account-hero__copy">
                <h1 className="account-hero__name">{profile?.name}</h1>
                <p className="account-hero__meta">{profile?.email || profile?.phone}</p>
                <p className="account-hero__meta">Member since {profile?.memberSince}</p>
            </div>
            <Link href="/account/profile" className="btn btn--ghost btn--compact account-hero__edit">
                Edit
            </Link>
        </section>
    );
}
