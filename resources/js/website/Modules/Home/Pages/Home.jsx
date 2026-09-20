import StorefrontLayout from '../../../Shared/Layouts/StorefrontLayout';
import BenefitsSection from '../Sections/BenefitsSection';
import CategoryScrollSection from '../Sections/CategoryScrollSection';
import FeaturedProductsSection from '../Sections/FeaturedProductsSection';
import HeroBannerSection from '../Sections/HeroBannerSection';
import LandscapePromoSection from '../Sections/LandscapePromoSection';
import NewsletterSection from '../Sections/NewsletterSection';
import OfferZoneSection from '../Sections/OfferZoneSection';

export default function Home({
    seo,
    quickCategories = [],
    heroBanners = [],
    offers = [],
    products = [],
    landscapeBanners = [],
    footerBanners = [],
    benefits = [],
}) {
    return (
        <StorefrontLayout seo={seo} current="home">
            <HeroBannerSection slides={heroBanners} />
            <CategoryScrollSection items={quickCategories} />
            <OfferZoneSection offers={offers} />
            <FeaturedProductsSection products={products} />
            <LandscapePromoSection banners={landscapeBanners} label="Middle banners" />
            <LandscapePromoSection banners={footerBanners} label="Footer banners" />
            <div className="desktop-enhance">
                <BenefitsSection benefits={benefits} />
                <NewsletterSection />
            </div>
        </StorefrontLayout>
    );
}
