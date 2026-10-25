import { useEffect, useId, useRef, useState } from 'react';
import ProductCard from '../../../Shared/Components/ProductCard';
import ListingFilterBar from '../../Product/Components/ListingFilterBar';
import ListingFilterSheet from '../../Product/Components/ListingFilterSheet';

const INITIAL_FILTERS = {
    category: null,
    q: null,
    size: null,
    color: null,
    sort: 'popular',
    price: null,
    min_price: null,
    max_price: null,
    rating: null,
};

export default function HomeProductFeed({ url }) {
    const titleId = useId();
    const sectionRef = useRef(null);
    const endRef = useRef(null);
    const requestId = useRef(0);
    const loadedPage = useRef(0);
    const [visible, setVisible] = useState(false);
    const [filters, setFilters] = useState(INITIAL_FILTERS);
    const [products, setProducts] = useState([]);
    const [filterOptions, setFilterOptions] = useState({});
    const [page, setPage] = useState(0);
    const [lastPage, setLastPage] = useState(1);
    const [total, setTotal] = useState(null);
    const [loading, setLoading] = useState(false);
    const [failed, setFailed] = useState(false);
    const [sheetPanel, setSheetPanel] = useState(null);
    const [attempt, setAttempt] = useState(0);

    useEffect(() => {
        const node = sectionRef.current;

        if (!node || visible) {
            return undefined;
        }

        const observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                setVisible(true);
            }
        }, { rootMargin: '280px' });

        observer.observe(node);

        return () => observer.disconnect();
    }, [visible]);

    useEffect(() => {
        if (!visible || !url) {
            return undefined;
        }

        const id = requestId.current + 1;
        requestId.current = id;
        const controller = new AbortController();

        setLoading(true);
        setFailed(false);
        setProducts([]);
        setPage(0);
        loadedPage.current = 0;

        fetch(`${url}?${queryString(filters, 1)}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: controller.signal,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Unable to load products.');
                }

                return response.json();
            })
            .then((data) => {
                if (id !== requestId.current) {
                    return;
                }

                setProducts(data.products ?? []);
                loadedPage.current = data.pagination?.page ?? 1;
                setPage(data.pagination?.page ?? 1);
                setLastPage(data.pagination?.lastPage ?? 1);
                setTotal(data.total ?? 0);
                setFilterOptions(data.filterOptions ?? {});
                setLoading(false);
            })
            .catch((error) => {
                if (error.name === 'AbortError' || id !== requestId.current) {
                    return;
                }

                setFailed(true);
                setLoading(false);
            });

        return () => {
            requestId.current += 1;
            controller.abort();
        };
    }, [visible, url, filters, attempt]);

    useEffect(() => {
        const node = endRef.current;

        if (!node || !visible || !url || loading || failed || page < 1 || page >= lastPage) {
            return undefined;
        }

        const observer = new IntersectionObserver((entries) => {
            if (!entries.some((entry) => entry.isIntersecting)) {
                return;
            }

            const nextPage = page + 1;

            if (nextPage <= loadedPage.current) {
                return;
            }

            observer.disconnect();
            loadedPage.current = nextPage;
            const id = requestId.current + 1;
            requestId.current = id;

            setLoading(true);

            fetch(`${url}?${queryString(filters, nextPage)}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Unable to load products.');
                    }

                    return response.json();
                })
                .then((data) => {
                    if (id !== requestId.current) {
                        return;
                    }

                    setProducts((current) => [...current, ...(data.products ?? [])]);
                    setPage(data.pagination?.page ?? nextPage);
                    setLastPage(data.pagination?.lastPage ?? lastPage);
                    setTotal(data.total ?? 0);
                    setLoading(false);
                })
                .catch(() => {
                    if (id !== requestId.current) {
                        return;
                    }

                    setFailed(true);
                    setLoading(false);
                });
        }, { rootMargin: '480px' });

        observer.observe(node);

        return () => observer.disconnect();
    }, [visible, url, loading, failed, page, lastPage, filters]);

    const applyFilters = (patch) => {
        setFilters((current) => ({ ...current, ...patch }));
        setSheetPanel(null);
    };

    const clearFilters = () => {
        setFilters(INITIAL_FILTERS);
    };

    const hasPriceFilter = filters.price != null || filters.min_price != null || filters.max_price != null;
    const activeCount = [hasPriceFilter, filters.rating != null, filters.size != null, filters.color != null].filter(Boolean).length;
    const loaded = page > 0 && !failed;

    return (
        <section ref={sectionRef} className="home-feed" aria-labelledby={titleId}>
            <div className="home-feed__head">
                <h2 id={titleId} className="home-feed__title">All products</h2>
                {total != null ? (
                    <p className="home-feed__count">{total === 1 ? '1 product' : `${total} products`}</p>
                ) : null}
            </div>

            {loaded ? (
                <ListingFilterBar
                    filters={filters}
                    activeCount={activeCount}
                    hasSizes={(filterOptions.sizes ?? []).length > 0}
                    onOpen={setSheetPanel}
                />
            ) : null}

            {products.length > 0 ? (
                <div className="product-grid product-grid--listing">
                    {products.map((product) => (
                        <ProductCard key={product.slug} product={product} titleAs="h3" />
                    ))}
                </div>
            ) : null}

            {loaded && products.length === 0 ? (
                <div className="listing__empty">
                    <p className="listing__empty-title">No products found</p>
                    <p className="listing__empty-text">Clear your filters and try again.</p>
                    <button type="button" className="btn btn--outline" onClick={clearFilters}>
                        Clear filters
                    </button>
                </div>
            ) : null}

            {loading ? <p className="home-feed__status">{products.length > 0 ? 'Loading more products…' : 'Loading products…'}</p> : null}

            {failed ? (
                <div className="listing__empty">
                    <p className="listing__empty-title">Couldn't load products</p>
                    <p className="listing__empty-text">Check your connection and try again.</p>
                    <button type="button" className="btn btn--outline" onClick={() => setAttempt((current) => current + 1)}>
                        Try again
                    </button>
                </div>
            ) : null}

            <div ref={endRef} className="home-feed__end" />

            <ListingFilterSheet
                panel={sheetPanel}
                filters={filters}
                options={filterOptions}
                onApply={applyFilters}
                onClose={() => setSheetPanel(null)}
            />
        </section>
    );
}

function queryString(filters, page) {
    const params = new URLSearchParams();
    const entries = {
        category: filters.category,
        q: filters.q,
        size: filters.size,
        color: filters.color,
        sort: filters.sort && filters.sort !== 'popular' ? filters.sort : null,
        price: filters.price,
        min_price: filters.min_price,
        max_price: filters.max_price,
        rating: filters.rating,
        page,
    };

    Object.entries(entries).forEach(([key, value]) => {
        if (value != null && value !== '') {
            params.set(key, String(value));
        }
    });

    return params.toString();
}
