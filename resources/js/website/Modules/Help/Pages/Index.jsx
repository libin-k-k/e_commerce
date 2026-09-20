import StorefrontLayout from '../../../Shared/Layouts/StorefrontLayout';
import HelpChatSection from '../Sections/HelpChatSection';

export default function Index({ seo, chat }) {
    return (
        <StorefrontLayout seo={seo} current="help" hideFooter hideHeaderOnMobile compactMain>
            <div className="help-page">
                <HelpChatSection chat={chat} />
            </div>
        </StorefrontLayout>
    );
}
