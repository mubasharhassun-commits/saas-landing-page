import { Routes, Route } from "react-router-dom";
import { Layout } from "@/components/Layout";
import { Home } from "@/pages/Home";
import { About } from "@/pages/About";
import { Contact } from "@/pages/Contact";
import { FAQ } from "@/pages/FAQ";
import { PricingPage } from "@/pages/PricingPage";
import { BlogIndex } from "@/pages/blog/BlogIndex";
import { BlogPost } from "@/pages/blog/BlogPost";
import { NotFound } from "@/pages/NotFound";

function App() {
  return (
    <Routes>
      <Route element={<Layout />}>
        <Route index element={<Home />} />
        <Route path="about" element={<About />} />
        <Route path="contact" element={<Contact />} />
        <Route path="faq" element={<FAQ />} />
        <Route path="pricing" element={<PricingPage />} />
        <Route path="blog" element={<BlogIndex />} />
        <Route path="blog/:slug" element={<BlogPost />} />
        <Route path="*" element={<NotFound />} />
      </Route>
    </Routes>
  );
}

export default App;
