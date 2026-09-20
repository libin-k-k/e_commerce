import AccountShell from '../Components/AccountShell';
import AccountSubpageHeader from '../Components/AccountSubpageHeader';
import FaqSection from '../Sections/FaqSection';

export default function Faq({ seo, faq = [], menu = [], profile, active = 'faq' }) {
    return (
        <AccountShell seo={seo} menu={menu} profile={profile} active={active}>
            <AccountSubpageHeader title="FAQ" />
            <FaqSection faq={faq} />
        </AccountShell>
    );
}
