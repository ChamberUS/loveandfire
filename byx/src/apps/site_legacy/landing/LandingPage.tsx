import LandingHeader from "./LandingHeader";
import HeroSection from "./HeroSection";
import MarketplaceSection from "./MarketplaceSection";
import HowItWorksSection from "./HowItWorksSection";
import FeaturesSection from "./FeaturesSection";
import ContactSection from "./ContactSection";
import LandingFooter from "./LandingFooter";

export default function LandingPage() {
  return (
    <div className="min-h-screen bg-[#070B0F] text-white">
      <LandingHeader />
      <main>
        <HeroSection />
        <MarketplaceSection />
        <HowItWorksSection />
        <FeaturesSection />
        <ContactSection />
      </main>
      <LandingFooter />
    </div>
  );
}
