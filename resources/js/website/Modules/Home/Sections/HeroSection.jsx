import { Link } from '@inertiajs/react';

export default function HeroSection({ eyebrow, title, text, primaryHref = '/products' }) {
    return (
        <section className="hero fade-in" aria-labelledby="hero-title">
            <div className="hero__inner">
                {eyebrow ? <p className="hero__eyebrow rise-in">{eyebrow}</p> : null}
                <h1 id="hero-title" className="hero__title rise-in rise-in-delay-1">
                    {title}
                </h1>
                {text ? (
                    <p className="hero__text rise-in rise-in-delay-2">{text}</p>
                ) : null}
                <div className="hero__actions rise-in rise-in-delay-3">
                    <Link href={primaryHref} className="btn btn--accent">
                        Shop now
                    </Link>
                    <Link href="/products" className="btn btn--ghost">
                        Browse categories
                    </Link>
                </div>
            </div>
        </section>
    );
}
