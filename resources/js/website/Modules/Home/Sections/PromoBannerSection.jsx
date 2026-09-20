import { Link } from '@inertiajs/react';

export default function PromoBannerSection({
    title = 'Weekend deals are live',
    text = 'Save on everyday essentials with limited-time offers.',
    href = '/products',
}) {
    return (
        <section className="section" aria-labelledby="promo-title">
            <div className="promo">
                <div className="promo__panel">
                    <div>
                        <h2 id="promo-title" className="promo__title">
                            {title}
                        </h2>
                        <p className="promo__text">{text}</p>
                    </div>
                    <Link href={href} className="btn btn--ghost">
                        Explore deals
                    </Link>
                </div>
            </div>
        </section>
    );
}
