import { usePage } from '@inertiajs/react';

export const BRAND_LOGO_SRC = '/assets/website/logo.png';

export default function BrandLogo({ className = 'brand-logo', alt }) {
    const { appName } = usePage().props;

    return <img className={className} src={BRAND_LOGO_SRC} alt={alt ?? appName} width={226} height={70} decoding="async" />;
}
