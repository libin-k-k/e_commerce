import StorefrontLayout from '../../../Shared/Layouts/StorefrontLayout';
import BenefitsSection from '../Sections/BenefitsSection';
import CategoryScrollSection from '../Sections/CategoryScrollSection';
import DealCardRail from '../Sections/DealCardRail';
import FlashDealsSection from '../Sections/FlashDealsSection';
import HeroBannerSection from '../Sections/HeroBannerSection';
import HomeProductFeed from '../Sections/HomeProductFeed';
import LandscapePromoSection from '../Sections/LandscapePromoSection';
import NewsletterSection from '../Sections/NewsletterSection';
import OfferZoneSection from '../Sections/OfferZoneSection';
import TopCategoriesSection from '../Sections/TopCategoriesSection';

export default function Home({
    seo,
    quickCategories = [],
    heroBanners = [],
    offers = [],
    offerPage = null,
    dealCards = [],
    deals = [],
    dealsBanner = null,
    landscapeBanners = [],
    footerBanners = [],
    benefits = [],
    homeProductsUrl,
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
                <OfferZoneSection offers={offers} offerPage={offerPage} />
                <DealCardRail cards={dealCards} />
                <LandscapePromoSection banners={landscapeBanners} label="Middle banners" />
                <LandscapePromoSection banners={footerBanners} label="Footer banners" />
                <HomeProductFeed url={homeProductsUrl} />
                <div className="desktop-enhance">
                    <NewsletterSection />
                </div>
            </div>
        </StorefrontLayout>
    );
}
