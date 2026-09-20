import { Link, usePage } from '@inertiajs/react';
import { useEffect, useId, useMemo, useState } from 'react';

export default function CategoryDrawer({ open, onClose }) {
    const { categoryMenu = [] } = usePage().props;
    const titleId = useId();
    const categories = useMemo(
        () => (Array.isArray(categoryMenu) ? categoryMenu : []),
        [categoryMenu],
    );
    const [activeId, setActiveId] = useState(categories[0]?.id ?? null);

    useEffect(() => {
        if (open && categories.length > 0) {
            setActiveId((current) => current ?? categories[0].id);
        }
    }, [open, categories]);

    useEffect(() => {
        if (!open) {
            return undefined;
        }

        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        document.body.classList.add('is-drawer-open');
        window.addEventListener('keydown', onKeyDown);

        return () => {
            document.body.classList.remove('is-drawer-open');
            window.removeEventListener('keydown', onKeyDown);
        };
    }, [open, onClose]);

    const active = categories.find((item) => item.id === activeId) ?? categories[0];

    return (
        <div
            className={`category-drawer${open ? ' is-open' : ''}`}
            role="dialog"
            aria-modal="true"
            aria-labelledby={titleId}
            aria-hidden={!open}
        >
            <div className="category-drawer__header">
                <button
                    type="button"
                    className="icon-btn"
                    aria-label="Close categories"
                    onClick={onClose}
                >
                    <CloseIcon />
                </button>
                <p id={titleId} className="category-drawer__title">
                    Categories
                </p>
                <span className="category-drawer__header-spacer" aria-hidden="true" />
            </div>

            <div className="category-drawer__body">
                <aside className="category-drawer__sidebar" aria-label="Main categories">
                    {categories.map((category) => (
                        <button
                            key={category.id}
                            type="button"
                            className={`category-rail__item${active?.id === category.id ? ' is-active' : ''}`}
                            onClick={() => setActiveId(category.id)}
                        >
                            {category.image ? (
                                <img
                                    className="category-rail__avatar"
                                    src={category.image}
                                    alt=""
                                    loading="lazy"
                                />
                            ) : (
                                <span
                                    className={`category-rail__avatar category-tone--${category.tone}`}
                                    aria-hidden="true"
                                />
                            )}
                            <span className="category-rail__label">{category.name}</span>
                        </button>
                    ))}
                </aside>

                <div className="category-drawer__panel">
                    {active ? (
                        <>
                            <p className="category-drawer__eyebrow">{active.name}</p>

                            {active.featured?.length > 0 ? (
                                <section className="category-featured" aria-label={active.featuredTitle}>
                                    <h2 className="category-drawer__heading">{active.featuredTitle}</h2>
                                    <div className="category-featured__row">
                                        {active.featured.map((item) => (
                                            <Link
                                                key={item.slug}
                                                href={`/products?category=${item.slug}`}
                                                className="category-chip"
                                                onClick={onClose}
                                            >
                                                {item.image ? (
                                                    <img
                                                        className="category-chip__media"
                                                        src={item.image}
                                                        alt=""
                                                        loading="lazy"
                                                    />
                                                ) : (
                                                    <span
                                                        className={`category-chip__media category-tone--${item.tone}`}
                                                        aria-hidden="true"
                                                    />
                                                )}
                                                <span className="category-chip__label">{item.name}</span>
                                            </Link>
                                        ))}
                                    </div>
                                </section>
                            ) : null}

                            <section aria-label={active.sectionTitle}>
                                <h2 className="category-drawer__heading">{active.sectionTitle}</h2>
                                <div className="category-subgrid">
                                    {active.children?.map((item) => (
                                        <Link
                                            key={item.slug}
                                            href={`/products?category=${item.slug}`}
                                            className="category-sub"
                                            onClick={onClose}
                                        >
                                            {item.image ? (
                                                <img
                                                    className="category-sub__media"
                                                    src={item.image}
                                                    alt=""
                                                    loading="lazy"
                                                />
                                            ) : (
                                                <span
                                                    className={`category-sub__media category-tone--${item.tone}`}
                                                    aria-hidden="true"
                                                />
                                            )}
                                            <span className="category-sub__label">{item.name}</span>
                                        </Link>
                                    ))}
                                </div>
                            </section>
                        </>
                    ) : null}
                </div>
            </div>
        </div>
    );
}

function CloseIcon() {
    return (
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        </svg>
    );
}
