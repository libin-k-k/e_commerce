import { usePage } from '@inertiajs/react';

export const BRAND_LOGO_SRC = '/assets/website/logo.png?v=20261101';

export default function BrandLogo({ className = 'brand-logo', alt }) {
    const { appName } = usePage().props;

    return <img className={className} src={BRAND_LOGO_SRC} alt={alt ?? appName} width={196} height={93} decoding="async" />;
}
