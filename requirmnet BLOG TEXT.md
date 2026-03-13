  
**CORPIUS**

**COMPLETE DEVELOPER BUG REPORT**

**EXECUTIVE SUMMARY**

This document is the result of a **two-round deep audit** of the entire CORPIUS website (corpius.net), conducted in March 2026\. All pages were manually reviewed including: Homepage, all 6 service pages, Pricing, About, Contact, Knowledge Base, Register, and Login.

The site has a strong professional design and solid information architecture. However, it contains **14 confirmed issues** — 7 of which are critical and carry direct legal, financial, or reputational risk. Every issue in this document must be addressed before the platform is considered production-ready.

**ALL ISSUES — MASTER OVERVIEW**

| \# | Issue Summary | Page(s) | Type | Priority |
| ----- | ----- | ----- | ----- | ----- |
| 1 | Raw i18n translation keys visible to users on Green Card page | /services/green-card-lottery | **Technical** | **CRITICAL** |
| 2 | CTA buttons on LLC page show wrong prices ($349 / $199) | /services/llc | **UX \+ Legal** | **CRITICAL** |
| 3 | Income Tax price conflict: $499 (homepage) vs $175 (pricing) | / and /pricing | **UX \+ Legal** | **CRITICAL** |
| 4 | About page: false stats — 2015/8yrs/10,000+ (must be 2020/5yrs/2,500+) | /about | **Legal** | **CRITICAL** |
| 5 | Register page: legal links (/terms, /privacy) lead to 404 | /register | **Legal** | **CRITICAL** |
| 6 | Green Card FAQ: outdated date — 'DV-2026 opens October 2024' | /services/green-card-lottery | **Content** | **CRITICAL** |
| 7 | Testimonials: unverified stock photos — FTC violation risk | / (homepage) | **Legal** | **CRITICAL** |
| 8 | 3 variants of login button: Login / Sign In / Log In | Nav \+ /login | UX | **HIGH** |
| 9 | Processing time: 4 different answers across pages | All pages | Content | **HIGH** |
| 10 | Footer: 'Blog' \= anchor \#, 'Help Center' \= anchor \# on homepage | / (homepage footer) | UX | **HIGH** |
| 11 | Pricing feature table: wrong column names (Basic / Professional) | /pricing | UX | **HIGH** |
| 12 | Income Tax 'Learn More' links to /contact not service page | / (homepage) | UX | **HIGH** |
| 13 | Income Tax service page has no pricing section | /services/income-tax-filing-planning | Content | **MEDIUM** |
| 14 | REVOLD AI chat widget only on /contact — must be global | All pages | Feature | **MEDIUM** |

   **PART 1 — CRITICAL BUGS: FIX IMMEDIATELY**

  **🆕  NEW FINDING — DISCOVERED IN AUDIT v2**  

  **🔴  CRITICAL — FIX IMMEDIATELY**  

**BUG \#1 — RAW i18n KEYS VISIBLE ON GREEN CARD PAGE**

FILE: **/services/green-card-lottery**

*PROBLEM: The i18n (internationalization) system has not injected real content into multiple sections of this page. Instead of text, users see raw translation key names — this makes the page completely unprofessional and broken.*

**Exact broken keys found on the page:**

* marketing.greencard\_why\_choose\_title

* marketing.greencard\_why\_choose\_1 / \_2 / \_3 / \_4

* marketing.greencard\_what\_is\_title

* marketing.greencard\_what\_is\_desc

* marketing.greencard\_education\_work

* marketing.greencard\_country\_verify

* marketing.greencard\_faq\_a3\_step\_1 through \_step\_5

* marketing.greencard\_faq\_a3\_note

**Fix Instructions:**

Step 1: Open your i18n config (e.g., en.json or locales/en.js)

Step 2: Verify all keys under 'marketing.greencard\_\*' are defined with real text

Step 3: Check that the translation provider (i18next / react-intl etc.) is initialized before the page renders

Step 4: If keys are missing, add them with correct English content

Step 5: Rebuild and verify all text appears correctly on the live page

  **🔴  CRITICAL — FIX IMMEDIATELY**  

**BUG \#2 — LLC PAGE CTA BUTTONS SHOW WRONG PRICES**

FILE: **/services/llc**

*PROBLEM: The LLC service page contains three different price references. The real packages start at $1,480 — but two CTA buttons on the same page show $349 and $199. This is a classic bait-and-switch pattern prohibited under FTC advertising regulations.*

| Location on Page | Current Text (WRONG) | Replace With (CORRECT) |
| ----- | ----- | ----- |
| CTA button — TOP of page | **"Start My LLC — $349 \+ State Fee"** | **"Get Started — View Packages"** |
| Package cards — MIDDLE | **$1,480 / $1,560 / $1,650** | CORRECT — do not change |
| CTA button — BOTTOM of page | **"Start Your LLC Today \- $199"** | **"Start Your LLC Today"** |

**Code Fix:**

// TOP BUTTON — find in component/page file:

BEFORE: "Start My LLC — $349 \+ State Fee"

AFTER:  "Get Started — View Packages"

// BOTTOM BUTTON — find in component/page file:

BEFORE: "Start Your LLC Today \- $199"

AFTER:  "Start Your LLC Today"

*RULE: Never put a specific dollar price inside a CTA button if that price does not match the actual package on the same page. Use neutral action text only.*

  **🆕  NEW FINDING — DISCOVERED IN AUDIT v2**  

  **🔴  CRITICAL — FIX IMMEDIATELY**  

**BUG \#3 — INCOME TAX PRICE CONFLICT: $499 vs $175**

*PROBLEM: Income Tax Filing service shows two completely different starting prices on two different pages of the same website. A user who sees $499 on the homepage and then navigates to /pricing sees $175 — or vice versa. This destroys credibility.*

| Page | Displayed Price | Action Required |
| ----- | ----- | ----- |
| Homepage (/) — service card | **Starting at $499** | Decide: is the lowest entry price $175 or $499? Unify. |
| /pricing — Income Tax section | **Starting at $175** | Must match homepage after decision is made. |
| /pricing — Personal Tax plan | $175 | Clarify: is this a subset of Income Tax or separate service? |
| /pricing — Corporate Tax plan | $500 | Cross-check with homepage 'Starting at $499'. |

**Recommended Resolution:**

* If $175 is the real entry point — update homepage card to 'Starting at $175'

* If $499 is the intended minimum — update /pricing page Starter to $499

* **All pricing must be 100% identical across every page of the site**

  **🔴  CRITICAL — FIX IMMEDIATELY**  

**BUG \#4 — ABOUT PAGE: FALSE COMPANY STATISTICS**

FILE: **/about  \+  homepage stats block**

*PROBLEM: The About page and homepage currently display historically inaccurate data. Publishing false founding dates and inflated client numbers constitutes false advertising under FTC regulations and can result in fines.*

| Field / Stat | Current Value (WRONG) | Correct Value (REPLACE WITH) |
| ----- | ----- | ----- |
| Founding year | **"Since 2015"** | **"Since 2020"** |
| Years of experience | **"8+ Years of Experience"** | **"5+ Years of Experience"** |
| Businesses formed | **"10,000+ Businesses Formed"** | **"2,500+ Businesses Formed"** |
| Satisfaction rate | "99% Customer Satisfaction" | Keep ONLY if there is verifiable data. Otherwise: remove or replace with '5-Star Rated Service' |

**Global Search & Replace (run across all files):**

Search: "Since 2015"        →  Replace: "Since 2020"

Search: "8+ Years"           →  Replace: "5+ Years"

Search: "10,000+"            →  Replace: "2,500+"

Search: "8+ Years of Experience" →  Replace: "5+ Years of Experience"

Also check: homepage hero stats block (4 stat badges) — update there too

  **🆕  NEW FINDING — DISCOVERED IN AUDIT v2**  

  **🔴  CRITICAL — FIX IMMEDIATELY**  

**BUG \#5 — REGISTER PAGE: LEGAL LINKS LEAD TO 404 ERRORS**

FILE: **/register**

*PROBLEM: The registration page links to /terms and /privacy for the legal agreements. But these pages do not exist — the actual documents are at /terms-of-service and /privacy-policy. A new user trying to review the Terms before signing up hits a 404 page. This is a legal compliance failure — users must be able to access legal documents at signup.*

| Link on /register | Current href (BROKEN) | Correct href (FIX TO) |
| ----- | ----- | ----- |
| Terms of Service link | **"/terms"** | **"/terms-of-service"** |
| Privacy Policy link | **"/privacy"** | **"/privacy-policy"** |

**Code Fix in /register page:**

BEFORE: \<a href="/terms"\>Terms of Service\</a\>

AFTER:  \<a href="/terms-of-service"\>Terms of Service\</a\>

BEFORE: \<a href="/privacy"\>Privacy Policy\</a\>

AFTER:  \<a href="/privacy-policy"\>Privacy Policy\</a\>

  **🆕  NEW FINDING — DISCOVERED IN AUDIT v2**  

  **🔴  CRITICAL — FIX IMMEDIATELY**  

**BUG \#6 — GREEN CARD PAGE: OUTDATED DATE IN FAQ**

FILE: **/services/green-card-lottery — FAQ section**

*PROBLEM: The FAQ on the Green Card page states that DV-2026 registration 'will likely open in October 2024.' It is now March 2026 — this information is 18 months out of date and displays completely wrong guidance to users.*

| Field | Current Text (WRONG) | Correct Replacement |
| ----- | ----- | ----- |
| DV Lottery date reference | **"Registration for DV-2026 will likely open in October 2024"** | **"Registration for DV-2027 will likely open in October 2026\. Check dvprogram.state.gov for official dates."** |

*RECOMMENDATION: For all date-sensitive content (lottery dates, IRS deadlines, state fees), use a CMS-managed field or add a 'Last updated: \[date\]' stamp so outdated content is easy to detect and fix.*

  **🔴  CRITICAL — FIX IMMEDIATELY**  

**BUG \#7 — TESTIMONIALS: UNVERIFIED / STOCK PHOTOS — FTC RISK**

FILE: **/ (homepage) — 'What Our Clients Say' section**

*PROBLEM: Three testimonials are displayed (Sarah Johnson / Michael Chen / Jessica Rodriguez) with photos loaded from root paths (/sarah-johnson.png etc). These appear to be stock or AI-generated photos rather than real verified clients. Under FTC Endorsement Guidelines (16 CFR Part 255), fake or unverifiable testimonials are illegal.*

**Three Options — Choose One:**

* **OPTION A (Best): Replace with real client testimonials. Get written permission. Include: first name, company name, state, service type.**

* Example: 'John M. | LLC Formation | Delaware | ★★★★★'

* **OPTION B (Safe): Remove the testimonials section entirely until real ones are collected.**

* Better to have no testimonials than fake ones

* **OPTION C (Minimum): Add a clear disclaimer under each testimonial.**

* "Results may vary. Testimonials are representative examples and may be composites."

*Do NOT delay this fix. FTC fines for fake testimonials start at $50,000 per violation.*

   **PART 2 — HIGH PRIORITY BUGS: FIX THIS WEEK**

  **🆕  NEW FINDING — DISCOVERED IN AUDIT v2**  

  **🟠  HIGH PRIORITY — FIX THIS WEEK**  

**BUG \#8 — THREE VARIANTS OF LOGIN BUTTON: Login / Sign In / Log In**

*PROBLEM: The same authentication action is labeled three different ways across the site. This is a UX consistency failure that makes the product look unpolished.*

| Location | Current Label (INCONSISTENT) | Action |
| ----- | ----- | ----- |
| Desktop navigation bar | **"Login"** | **Change to: Sign In** |
| Mobile navigation bar | "Sign In" | Keep as: Sign In — this is correct |
| /login page — submit button | **"Log In"** | **Change to: Sign In** |
| /register page — bottom link | "Sign in" | Keep as: Sign in — this is correct |

// Standardized label to use EVERYWHERE: "Sign In"

// Update desktop nav component: "Login" → "Sign In"

// Update /login page submit button: "Log In" → "Sign In"

  **🟠  HIGH PRIORITY — FIX THIS WEEK**  

**BUG \#9 — PROCESSING TIME: 4 DIFFERENT ANSWERS ACROSS PAGES**

*PROBLEM: A user browsing multiple pages will see 4 completely different answers about how long business formation takes. This is contradictory and destroys trust.*

| Page | Current Processing Time Statement | Action |
| ----- | ----- | ----- |
| Homepage (Hero \+ stats badge) | 1–3 days | KEEP — but add 'business' → '1–3 business days' |
| /services/llc — FAQ | 1–3 business days (expedited) / 5–10 days (standard) | DELETE — replace with standard text below |
| /services/c-corporation — FAQ | 1–3 weeks (standard) / 3–5 days (expedited) | DELETE — replace with standard text below |
| /contact — FAQ | 3–7 business days | UPDATE — replace with standard text below |
| /pricing — footer note | Most companies formed within 1–3 business days | KEEP — this is the approved version |

**Approved Universal Copy — Use Everywhere:**

"Standard processing: 3–7 business days."

"Expedited processing: 1–3 business days (available at checkout)."

// Do a global search and replace across ALL page files

// No page should contradict this approved wording

  **🟠  HIGH PRIORITY — FIX THIS WEEK**  

**BUG \#10 — HOMEPAGE FOOTER: BLOG AND HELP CENTER ARE BROKEN (\#)**

FILE: **/ (homepage) — footer component**

*PROBLEM: On the homepage, the Blog and Help Center footer links point to '\#' (empty anchor — page does not scroll anywhere). On ALL other pages these links already point to /knowledge-base and /contact correctly. This means the homepage footer component was not updated when the others were.*

| Footer Link | Homepage href (BROKEN) | All other pages href (CORRECT) | Action |
| ----- | ----- | ----- | ----- |
| "Blog" | **"\#"** | "/knowledge-base" | Fix: href="/knowledge-base", label="Knowledge Base" |
| "Help Center" | **"\#"** | "/contact" | Fix: href="/contact", label="Contact Support" |

// In the HOMEPAGE footer (check if it uses a separate component):

BEFORE: \<a href="\#"\>Blog\</a\>

AFTER:  \<a href="/knowledge-base"\>Knowledge Base\</a\>

BEFORE: \<a href="\#"\>Help Center\</a\>

AFTER:  \<a href="/contact"\>Contact Support\</a\>

  **🟠  HIGH PRIORITY — FIX THIS WEEK**  

**BUG \#11 — PRICING PAGE: FEATURE TABLE COLUMN NAMES ARE WRONG**

FILE: **/pricing — 'What's Included' comparison table**

*PROBLEM: The feature comparison table uses column headers 'Basic' and 'Professional' — but these package names do not exist anywhere else on the site. Every other page uses 'Starter', 'Standard', 'Premium'. This confuses users comparing packages.*

| Table Column | Current Name (WRONG) | Correct Name (FIX TO) |
| ----- | ----- | ----- |
| Column 1 header | **"Basic"** | **"Starter"** |
| Column 2 header | **"Professional"** | **"Standard"** |
| Column 3 header | "Premium" | "Premium" ✓ — correct, keep as is |

// In /pricing — feature comparison table component:

BEFORE: Column headers \= \["Basic", "Professional", "Premium"\]

AFTER:  Column headers \= \["Starter", "Standard", "Premium"\]

  **🟠  HIGH PRIORITY — FIX THIS WEEK**  

**BUG \#12 — HOMEPAGE: INCOME TAX 'LEARN MORE' LINKS TO /contact**

FILE: **/ (homepage) — Income Tax Filing & Planning service card**

*PROBLEM: The Income Tax service card on the homepage has a 'Learn More' button that links to /contact. The actual service page at /services/income-tax-filing-planning exists and is fully built. This is a direct conversion loss.*

// In homepage service cards section — Income Tax card:

BEFORE: \<a href="/contact"\>Learn More\</a\>

AFTER:  \<a href="/services/income-tax-filing-planning"\>Learn More\</a\>

   **PART 3 — MEDIUM PRIORITY: NEXT SPRINT**

  **🟡  MEDIUM — NEXT SPRINT**  

**IMPROVEMENT \#13 — INCOME TAX SERVICE PAGE HAS NO PRICING SECTION**

FILE: **/services/income-tax-filing-planning**

*ISSUE: This is the only service page on the entire site that has zero pricing information. The 'pay after review' model is good — but users have no idea what range to expect. The /pricing page shows $175–$500 but the service page says nothing about price.*

**Recommended Fix:**

* Add an 'Estimated Pricing' section to the page with approximate ranges

* Example: 'Personal tax return — from $175 | Corporate return — from $500 | Payroll taxes — from $300'

* Add clear note: 'Final price confirmed after document review — no upfront payment required'

* Link to /pricing page for full details

  **🟡  MEDIUM — NEXT SPRINT**  

**IMPROVEMENT \#14 — REVOLD AI CHAT WIDGET IS NOT GLOBAL**

*CURRENT STATE: The CORPIUS AI Assistant (Powered by REVOLD AI) is only visible on /contact page. Users on the LLC page, pricing page, or any service page have no access to it.*

*WHY IT MATTERS: The AI chat is a competitive advantage over LegalZoom and Incfile who charge for support. A user with a question during /pricing or /services/llc browsing will convert 3x more if they get an instant AI answer. Don't hide this tool on /contact only.*

**Implementation:**

// Move AI chat widget from /contact page-level to global layout:

// File: \_app.jsx OR \_layout.jsx OR layout.tsx (root layout file)

// Place the \<CorporiusAIChat /\> component OUTSIDE the page router

// It should persist across all page navigations automatically

// Verify: widget appears on /, /pricing, all /services/\*, /about, /knowledge-base

   **PART 4 — DEVELOPER COMPLETION CHECKLIST**

**Complete every item below in order. Do not mark complete until verified on live site.**

**CRITICAL — Complete First (Bugs 1–7):**

* BUG 1: Fix all broken i18n keys on /services/green-card-lottery

* BUG 2: Remove price from LLC CTA top button ($349 → 'Get Started — View Packages')

* BUG 2: Remove price from LLC CTA bottom button ($199 → 'Start Your LLC Today')

* BUG 3: Resolve Income Tax price conflict — unify $175 vs $499 across all pages

* BUG 4: Update About \+ homepage stats: 2015 → 2020, 8+ → 5+, 10,000+ → 2,500+

* BUG 5: Fix /register legal links: /terms → /terms-of-service, /privacy → /privacy-policy

* BUG 6: Update Green Card FAQ date: 'DV-2026 October 2024' → 'DV-2027 October 2026'

* BUG 7: Verify testimonials — replace stock photos with real clients or remove section

**HIGH PRIORITY — Complete Same Week (Bugs 8–12):**

* BUG 8: Standardize all auth buttons to 'Sign In' — desktop nav, /login button

* BUG 9: Replace all processing time variants with approved universal copy

* BUG 10: Fix homepage footer — 'Blog' and 'Help Center' anchors (\#) to real URLs

* BUG 11: Fix /pricing feature table column names: Basic → Starter, Professional → Standard

* BUG 12: Fix homepage Income Tax 'Learn More' href → /services/income-tax-filing-planning

**MEDIUM — Complete Next Sprint (13–14):**

* IMPROVEMENT 13: Add estimated pricing section to /services/income-tax-filing-planning

* IMPROVEMENT 14: Move REVOLD AI chat widget to global layout (all pages)

**POST-FIX QA VERIFICATION TABLE**

| Page to Test | What to Verify After Fix |
| ----- | ----- |
| /services/green-card-lottery | All text sections render correctly — no raw key strings visible anywhere |
| /services/llc | CTA buttons show no dollar prices. Packages show $1,480 / $1,560 / $1,650 only. |
| / and /pricing | Income Tax starting price is identical on both pages |
| /about | Stats show: Since 2020, 5+ Years, 2,500+ Businesses |
| / (homepage stats block) | Stats badges are updated to match About page |
| /register | Terms of Service and Privacy Policy links open correct pages (no 404\) |
| /services/green-card-lottery — FAQ | DV Lottery date references are current and accurate |
| / (homepage testimonials) | Photos are real or section removed / disclaimer added |
| Desktop nav \+ /login | All auth buttons say 'Sign In' consistently |
| All pages — footer | Blog → /knowledge-base, Help Center → /contact (no \# anchors) |
| /pricing — table | Column headers say: Starter, Standard, Premium |
| / (homepage Income Tax card) | Learn More button goes to /services/income-tax-filing-planning |
| All pages | Processing time copy is consistent everywhere |
| All pages | REVOLD AI chat widget visible and functional on every page |

   **PART 5 — WHAT IS WORKING WELL (DO NOT CHANGE)**

The following elements are correctly implemented and should **not be altered:**

| Element | Status | Notes |
| ----- | ----- | ----- |
| Site navigation structure | **GOOD** | Logical, consistent across all pages |
| SEO page titles | **GOOD** | Well-structured, keyword-rich titles on all pages |
| Service page FAQ sections | **GOOD** | Great for SEO and conversion — keep on all service pages |
| Knowledge Base content | **GOOD** | Strong educational content, good for organic traffic |
| Full legal footer (7 documents) | **GOOD** | Privacy, ToS, Cookie, Compliance, Refund, Disclaimer, Security |
| S-Corporation eligibility section | **GOOD** | Qualify/Don't Qualify format is clear and professional |
| Nonprofit formation process steps (1–4) | **GOOD** | Well-structured visual flow |
| Income Tax: 'pay after review' model | **GOOD** | Strong differentiator — clearly explained |
| Income Tax expense categorization table | **GOOD** | Professional, IRS-aligned, builds trust |
| C-Corp add-ons section | **GOOD** | EIN, Corporate Kit, e-Kit, DBA — clear pricing |
| Non-U.S. residents can form C-Corp FAQ | **GOOD** | Important differentiator for international audience |
| REVOLD AI chat widget (design \+ concept) | **GREAT** | Powerful feature — just needs to be global (see Bug \#14) |
| Copyright © 2026 | **GOOD** | Current year, correct |
| Mobile navigation structure | **GOOD** | Complete service list, properly organized |
| 100% Money Back Guarantee badge | **GOOD** | Strong conversion element — keep prominent |
| Multi-language switcher | **GOOD** | Good for international audience — fix i18n first (Bug \#1) |

  **Questions or blockers? Contact Roman directly before making any assumptions or alternative decisions.**  

*© 2026 CORPIUS  —  Internal Document  —  Prepared by Roman  —  Do Not Distribute*