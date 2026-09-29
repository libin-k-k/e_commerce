import StorefrontLayout from '../../../Shared/Layouts/StorefrontLayout';
import BenefitsSection from '../Sections/BenefitsSection';
import CategoryScrollSection from '../Sections/CategoryScrollSection';
import FeaturedProductsSection from '../Sections/FeaturedProductsSection';
import FlashDealsSection from '../Sections/FlashDealsSection';
import HeroBannerSection from '../Sections/HeroBannerSection';
import LandscapePromoSection from '../Sections/LandscapePromoSection';
import NewsletterSection from '../Sections/NewsletterSection';
import OfferZoneSection from '../Sections/OfferZoneSection';
import TopCategoriesSection from '../Sections/TopCategoriesSection';

export default function Home({
    seo,
    quickCategories = [],
    heroBanners = [],
    offers = [],
    products = [],
    deals = [],
    dealsBanner = null,
    landscapeBanners = [],
    footerBanners = [],
    benefits = [],
}) {
    return (
        <StorefrontLayout seo={seo} current="home">
            <div className="home">
                <CategoryScrollSection items={quickCategories} />
                <HeroBannerSection slides={heroBanners} />
                <div className="desktop-enhance home__desktop">
                    <BenefitsSection benefits={benefits} />
                    <FlashDealsSection products={deals} banner={dealsBanner} />
                    <TopCategoriesSection />
                </div>
                <OfferZoneSection offers={offers} />
                <FeaturedProductsSection products={products} />
                <LandscapePromoSection banners={landscapeBanners} label="Middle banners" />
                <LandscapePromoSection banners={footerBanners} label="Footer banners" />
                <div className="desktop-enhance">
                    <NewsletterSection />
                </div>
            </div>
        </StorefrontLayout>
    );
}
