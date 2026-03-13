<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Security Policy | CORPIUS">
        <meta head-key="description" name="description" content="Read CORPIUS's Security Policy and Data Protection Statement describing how we secure client data, protect uploaded documents, and maintain regulatory compliance." />
        <link head-key="canonical" rel="canonical" href="https://corpius.net/security-policy" />
        <meta head-key="og:title" property="og:title" content="Security Policy & Data Protection | CORPIUS" />
        <meta head-key="og:description" property="og:description" content="CORPIUS Security Policy describing how we secure client data, protect documents, and maintain cybersecurity standards." />
        <meta head-key="og:url" property="og:url" content="https://corpius.net/security-policy" />
        <meta head-key="twitter:title" name="twitter:title" content="Security Policy | CORPIUS" />
        <meta head-key="twitter:description" name="twitter:description" content="CORPIUS Security Policy describing how we secure client data, protect documents, and maintain cybersecurity standards." />
    </Head>

    <MarketingLayout>
        <!-- Hero -->
        <section class="bg-gradient-to-br from-navy-900 via-blue-900 to-navy-900 py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl lg:text-5xl font-bold text-white mb-4">
                    Security Policy &amp; <span class="text-yellow-400">Data Protection</span>
                </h1>
                <p class="text-gray-300 text-lg">Last Updated: March 2, 2026</p>
            </div>
        </section>

        <!-- Content -->
        <section class="py-16" style="background-color: #0b1e33;">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="prose-legal">

                    <div class="legal-section">
                        <p class="legal-p">CORPIUS is committed to maintaining the highest standards of data protection, cybersecurity, privacy, and regulatory compliance across all services delivered through <a href="https://corpius.net" class="legal-link">https://corpius.net</a>. This Security Policy and Data Protection Statement describes how we secure client data, protect uploaded documents, operate our infrastructure, manage internal access, monitor risks, and ensure compliance with applicable U.S. and international standards.</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">1. Purpose and Scope</h2>
                        <p class="legal-p">This Policy applies to all business formation services, income tax filing &amp; planning services, EIN applications, document processing, identity verification, compliance checks, and all internal and external systems used to store or process client data.</p>
                        <p class="legal-p">The Policy covers all users, employees, contractors, consultants, and any authorized individuals who interact with CORPIUS systems.</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">2. Data Security Principles</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div class="legal-principle-card">
                                <h3 class="text-yellow-400 font-bold mb-1">Confidentiality</h3>
                                <p class="text-gray-300 text-sm">Client information is accessible only to authorized personnel with a legitimate business need.</p>
                            </div>
                            <div class="legal-principle-card">
                                <h3 class="text-yellow-400 font-bold mb-1">Integrity</h3>
                                <p class="text-gray-300 text-sm">Client data remains accurate, complete, unaltered, and protected against unauthorized modification.</p>
                            </div>
                            <div class="legal-principle-card">
                                <h3 class="text-yellow-400 font-bold mb-1">Availability</h3>
                                <p class="text-gray-300 text-sm">We maintain secure, stable infrastructure designed for continuous access with minimal downtime.</p>
                            </div>
                            <div class="legal-principle-card">
                                <h3 class="text-yellow-400 font-bold mb-1">Accountability</h3>
                                <p class="text-gray-300 text-sm">All employees and contractors must comply with strict internal security controls and logging policies.</p>
                            </div>
                            <div class="legal-principle-card sm:col-span-2">
                                <h3 class="text-yellow-400 font-bold mb-1">Minimum Necessary Rule</h3>
                                <p class="text-gray-300 text-sm">We collect and use only the minimum amount of data required to provide our services.</p>
                            </div>
                        </div>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">3. Data Collection and Retention</h2>
                        <h3 class="legal-h3">3.1 Data We Collect</h3>
                        <ul class="legal-ul">
                            <li>Identity information and business formation documents</li>
                            <li>Uploaded IDs and verification documents</li>
                            <li>Bank statements and financial records</li>
                            <li>Tax documents and prior returns</li>
                            <li>Contact information and communication logs</li>
                            <li>Service usage data</li>
                        </ul>
                        <h3 class="legal-h3">3.2 Data Retention</h3>
                        <ul class="legal-ul">
                            <li>Tax documentation: minimum 7 years</li>
                            <li>Corporate documents: permanent retention</li>
                            <li>Communications &amp; support logs: as long as operationally required</li>
                        </ul>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">4. Technical Safeguards (Cybersecurity Controls)</h2>
                        <p class="legal-p">CORPIUS uses a multilayered cybersecurity framework:</p>

                        <h3 class="legal-h3">4.1 Encryption</h3>
                        <ul class="legal-ul">
                            <li>Data in transit: encrypted via HTTPS/TLS 1.2+</li>
                            <li>Data at rest: stored in encrypted environments</li>
                            <li>Sensitive data (IDs, tax documents): stored using additional encryption layers</li>
                        </ul>

                        <h3 class="legal-h3">4.2 Secure Infrastructure</h3>
                        <ul class="legal-ul">
                            <li>Hardened cloud architecture with firewalls and network segmentation</li>
                            <li>Encrypted storage systems and secure backup procedures</li>
                            <li>DDoS protection and geo-redundant data replication</li>
                        </ul>

                        <h3 class="legal-h3">4.3 Access Control</h3>
                        <ul class="legal-ul">
                            <li>Strict role-based access (RBAC)</li>
                            <li>Multi-factor authentication (MFA) for admin systems</li>
                            <li>Logged and monitored access sessions</li>
                            <li>Automatic access revocation upon personnel changes</li>
                            <li>"Least privilege" principle enforced at all levels</li>
                        </ul>

                        <h3 class="legal-h3">4.4 Monitoring &amp; Threat Detection</h3>
                        <ul class="legal-ul">
                            <li>Continuous system monitoring and automated threat detection</li>
                            <li>Tamper detection and intrusion alerts</li>
                            <li>Real-time event logging and audit trails for sensitive actions</li>
                        </ul>

                        <h3 class="legal-h3">4.5 Backup &amp; Disaster Recovery</h3>
                        <ul class="legal-ul">
                            <li>Daily encrypted backups and redundant data centers</li>
                            <li>Tested recovery procedures and incident simulation drills</li>
                        </ul>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">5. Organizational &amp; Administrative Safeguards</h2>
                        <h3 class="legal-h3">5.1 Employee Training</h3>
                        <p class="legal-p">All personnel undergo mandatory training on data protection, security protocols, phishing prevention, secure document handling, and privacy laws.</p>
                        <h3 class="legal-h3">5.2 Confidentiality Obligations</h3>
                        <p class="legal-p">Employees and contractors sign NDAs, access agreements, and security compliance acknowledgements.</p>
                        <h3 class="legal-h3">5.3 Internal Audits</h3>
                        <p class="legal-p">We perform routine audits, quarterly reviews, random compliance checks, document accuracy audits, and system integrity assessments.</p>
                        <h3 class="legal-h3">5.4 Vendor &amp; Third-Party Compliance</h3>
                        <p class="legal-p">Vendors must meet strict security requirements including confidentiality obligations, encryption standards, and SOC-2 / ISO-27001 compliant environments where applicable.</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">6. Document Upload &amp; File Protection</h2>
                        <p class="legal-p">When users upload documents (IDs, bank statements, IRS forms, corporate records):</p>
                        <ul class="legal-ul">
                            <li>Files are transmitted via secure encrypted channels</li>
                            <li>Stored in encrypted form and accessible only by authorized staff</li>
                            <li>Logged for traceability and monitored for unauthorized access</li>
                            <li>Never shared with third parties outside service necessity</li>
                        </ul>
                        <p class="legal-p">Documents are not used for training AI models. Documents are never sold or disclosed outside the scope of work.</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">7. AI &amp; Automation Security Compliance</h2>
                        <p class="legal-p">CORPIUS uses AI systems for document analysis, OCR extraction, expense categorization, data verification, and workflow optimization. Security guarantees include: AI does not access client data beyond scope of service, no autonomous decisions without human review, no sensitive data used for training external AI systems, and all AI processing remains internal and compliant with privacy requirements.</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">8. Fraud, Abuse &amp; Identity Theft Prevention</h2>
                        <p class="legal-p">CORPIUS employs multi-layer protection including identity verification checks, anomaly detection in document uploads, device fingerprinting, transaction risk scoring, manual compliance review for high-risk cases, enhanced verification for suspicious activity, secure audit trail retention, KYC (Know Your Customer) standards, and AML (Anti-Money-Laundering) checks.</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">9. Data Sharing &amp; Disclosure</h2>
                        <h3 class="legal-h3">9.1 Permitted Sharing</h3>
                        <p class="legal-p">Filing state agencies for formation, IRS for EIN or tax filings, third-party service providers (e-signing systems, secure storage, payment processors), and legal advisors, accountants, auditors.</p>
                        <h3 class="legal-h3">9.2 Prohibited Sharing</h3>
                        <p class="legal-p">We never share, sell, or disclose client data for marketing by third parties, for unrelated commercial purposes, for AI model training, or without lawful justification.</p>
                        <h3 class="legal-h3">9.3 Disclosure For Legal Reasons</h3>
                        <p class="legal-p">We may disclose information only when required by court order, required by government request under applicable law, or necessary to protect CORPIUS from fraud or security threats.</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">10. Physical Security</h2>
                        <ul class="legal-ul">
                            <li>Restricted facility access and video monitoring</li>
                            <li>Locked storage for sensitive documents</li>
                            <li>Secure shredding of retired documents</li>
                            <li>Visitor logs and badge controls</li>
                            <li>Environmental protection (fire/flood controls)</li>
                        </ul>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">11. Incident Response &amp; Breach Management</h2>
                        <p class="legal-p">If a suspected or confirmed security incident occurs, CORPIUS will:</p>
                        <h3 class="legal-h3">11.1 Immediate Actions</h3>
                        <p class="legal-p">Isolate affected systems, activate incident response team, and secure vulnerable environments.</p>
                        <h3 class="legal-h3">11.2 Investigation</h3>
                        <p class="legal-p">Determine scope, assess data affected, identify root cause, and document findings.</p>
                        <h3 class="legal-h3">11.3 Mitigation</h3>
                        <p class="legal-p">Patch vulnerabilities, prevent recurrence, and restore systems from secure backups.</p>
                        <h3 class="legal-h3">11.4 Notification</h3>
                        <p class="legal-p">If required by law, affected users will be notified within the legally mandated timeframe. We may provide assistance with mitigation steps following a breach. See our full <a href="/incident-response-policy" class="legal-link">Incident Response Policy</a> for details.</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">12. User Responsibilities</h2>
                        <p class="legal-p">To maintain system security, clients must:</p>
                        <ul class="legal-ul">
                            <li>Upload accurate, legitimate documents</li>
                            <li>Use strong passwords and keep login credentials confidential</li>
                            <li>Notify CORPIUS of suspicious account activity</li>
                            <li>Avoid using public Wi-Fi for sensitive uploads</li>
                            <li>Refrain from fraudulent or illegal activities</li>
                        </ul>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">13. Compliance With Global Standards</h2>
                        <h3 class="legal-h3">13.1 GDPR (EU) / UK GDPR</h3>
                        <p class="legal-p">Lawful basis for processing, rights of access, correction, deletion, secure cross-border transfer mechanisms, and data minimization.</p>
                        <h3 class="legal-h3">13.2 CCPA / CPRA (California)</h3>
                        <p class="legal-p">Transparency, limited use of personal data, right to access &amp; delete, and "Do Not Sell or Share" compliance if triggered.</p>
                        <h3 class="legal-h3">13.3 IRS Data Protection Requirements</h3>
                        <p class="legal-p">Including requirements specific to tax-document handling, EIN applications, identity verification, and secure storage of tax records.</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">14. Policy Updates</h2>
                        <p class="legal-p">We may update this Policy at any time due to legal or regulatory changes, security upgrades, new service features, updated compliance controls, or internal workflow changes. All updates become effective immediately upon posting on the Site.</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">15. Contact Information</h2>
                        <div class="legal-contact-box">
                            <p class="text-white font-semibold mb-2">CORPIUS — Security &amp; Compliance Department</p>
                            <p>Email: <a href="mailto:admin@corpius.net" class="legal-link">admin@corpius.net</a></p>
                            <p>Website: <a href="https://corpius.net" class="legal-link">https://corpius.net</a></p>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </MarketingLayout>
</template>

<style scoped>
.legal-section {
    @apply mb-10;
}
.legal-h2 {
    @apply text-2xl font-bold text-yellow-400 mb-4 mt-8 border-b border-blue-800 pb-2;
}
.legal-h3 {
    @apply text-lg font-semibold text-white mb-2 mt-5;
}
.legal-p {
    @apply text-gray-300 leading-relaxed mb-4;
}
.legal-ul {
    @apply list-disc list-inside text-gray-300 mb-4 space-y-1 pl-4;
}
.legal-link {
    @apply text-yellow-400 hover:text-yellow-300 underline;
}
.legal-contact-box {
    @apply bg-blue-900/40 border border-blue-700 rounded-lg p-6 mb-4 text-gray-300;
}
.legal-principle-card {
    @apply bg-blue-900/40 border border-blue-700 rounded-lg p-4;
}
</style>
