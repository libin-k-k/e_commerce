import { Head, usePage } from '@inertiajs/react';

export default function SeoHead({ seo }) {
    const { appName } = usePage().props;
    const title = seo?.title ?? appName;
    const description = seo?.description ?? '';
    const keywords = Array.isArray(seo?.keywords)
        ? seo.keywords.join(', ')
        : (seo?.keywords ?? '');

    return (
        <Head>
            <title>{title}</title>
            <meta head-key="description" name="description" content={description} />
            <meta head-key="keywords" name="keywords" content={keywords} />
        </Head>
    );
}
