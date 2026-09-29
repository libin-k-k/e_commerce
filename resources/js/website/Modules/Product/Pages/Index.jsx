import { router } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import ProductCard from '../../../Shared/Components/ProductCard';
import StorefrontLayout from '../../../Shared/Layouts/StorefrontLayout';
import ListingBanner from '../Components/ListingBanner';
import ListingCategoryChips from '../Components/ListingCategoryChips';
import ListingFilterBar from '../Components/ListingFilterBar';
import ListingFilterSheet from '../Components/ListingFilterSheet';
import ListingHeader from '../Components/ListingHeader';
import ListingPagination from '../Components/ListingPagination';
import ListingSidebar from '../Components/ListingSidebar';
import ListingToolbar from '../Components/ListingToolbar';

const DEFAULT_SORT = 'popular';
const DEFAULT_PER_PAGE = 12;
const VIEW_STORAGE_KEY = 'listing-view';

const isDefault = (key, value) =>
    (key === 'sort' && value === DEFAULT_SORT) ||
    (key === 'per_page' && Number(value) === DEFAULT_PER_PAGE) ||
    (key === 'page' && Number(value) <= 1);

const toQuery = (filters) =>
    Object.fromEntries(
        Object.entries(filters).filter(
            ([key, value]) => value !== null && value !== undefined && value !== '' && !isDefault(key, value),
        ),
    );

export default function Index({
    seo,
    products = [],
    total = 0,
    pagination = null,
    category = null,
    chips = { allSlug: null, activeSlug: null, items: [] },
    categoryTree = { total: 0, items: [] },
    filters = {},
    filterOptions = {},
    banner = null,
}) {
    const [sheetPanel, setSheetPanel] = useState(null);
    const [view, setView] = useState('grid');
    const closeSheet = useCallback(() => setSheetPanel(null), []);

    useEffect(() => {
        if (window.localStorage.getItem(VIEW_STORAGE_KEY) === 'list') {
            setView('list');
        }
    }, []);

    const changeView = (nextView) => {
        setView(nextView);
        window.localStorage.setItem(VIEW_STORAGE_KEY, nextView);
    };

    const visit = (query, preserveScroll) => {
        router.get('/products', toQuery(query), { preserveState: true, preserveScroll });
    };

    const applyFilters = (changes) => {
        const next = { ...filters, ...changes };

        if (changes.price) {
            next.min_price = null;
            next.max_price = null;
        }

        visit(next, true);
    };

    const goToPage = (page) => visit({ ...filters, page }, false);

    const clearFilters = () => {
        applyFilters({
            q: null,
            size: null,
            color: null,
            price: null,
            min_price: null,
            max_price: null,
            rating: null,
            sort: DEFAULT_SORT,
        });
    };

    const selectCategory = (slug) => applyFilters({ category: slug, size: null, color: null });

    const hasPriceFilter = filters.price != null || filters.min_price != null || filters.max_price != null;
    const activeCount = [hasPriceFilter, filters.rating != null, filters.size != null, filters.color != null].filter(
        Boolean,
    ).length;

    const title = category?.name ?? (filters.q ? `“${filters.q}”` : 'All Products');
    const subtitle = filters.q
        ? `Search results for “${filters.q}”`
        : category?.description || 'Discover our wide range of products';

    return (
        <StorefrontLayout seo={seo} current="products" hideHeaderOnMobile>
            <div className={`listing listing--${view}`}>
                <ListingHeader
                    title={title}
                    total={total}
                    query={filters.q ?? ''}
                    onSearch={(q) => applyFilters({ q })}
                />

                <ListingToolbar
                    title={title}
                    subtitle={subtitle}
                    total={total}
                    category={category}
                    sort={filters.sort ?? DEFAULT_SORT}
                    sorts={filterOptions.sorts ?? []}
                    view={view}
                    onSort={(sort) => applyFilters({ sort })}
                    onViewChange={changeView}
                />

                <div className="listing__layout">
                    <ListingSidebar
                        tree={categoryTree}
                        filters={filters}
                        options={filterOptions}
                        activeCount={activeCount}
                        onApply={applyFilters}
                        onCategory={selectCategory}
                        onClear={clearFilters}
                    />

                    <div className="listing__main">
                        {banner ? <ListingBanner banner={banner} /> : null}

                        <ListingCategoryChips
                            chips={chips}
                            onSelect={(slug) => selectCategory(slug ?? chips.allSlug)}
                        />

                        <ListingFilterBar
                            filters={filters}
                            activeCount={activeCount}
                            hasSizes={(filterOptions.sizes ?? []).length > 0}
                            onOpen={setSheetPanel}
                        />

                        {products.length > 0 ? (
                            <div className={`product-grid product-grid--listing${view === 'list' ? ' is-list' : ''}`}>
                                {products.map((product) => (
                                    <ProductCard key={product.slug} product={product} titleAs="h2" />
                                ))}
                            </div>
                        ) : (
                            <div className="listing__empty">
                                <p className="listing__empty-title">No products found</p>
                                <p className="listing__empty-text">Try a different category or clear your filters.</p>
                                <button type="button" className="btn btn--outline" onClick={clearFilters}>
                                    Clear filters
                                </button>
                            </div>
                        )}

                        {pagination ? (
                            <ListingPagination
                                pagination={pagination}
                                total={total}
                                onPage={goToPage}
                                onPerPage={(perPage) => applyFilters({ per_page: perPage })}
                            />
                        ) : null}
                    </div>
                </div>
            </div>

            <ListingFilterSheet
                panel={sheetPanel}
                filters={filters}
                options={filterOptions}
                onApply={applyFilters}
                onClose={closeSheet}
            />
        </StorefrontLayout>
    );
}
