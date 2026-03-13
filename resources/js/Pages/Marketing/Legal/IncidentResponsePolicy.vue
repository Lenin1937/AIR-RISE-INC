<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Incident Response Policy | CORPIUS">
        <meta head-key="description" name="description" content="CORPIUS Incident Response Policy establishing procedures for identifying, reporting, containing, and resolving security incidents involving our systems and client data." />
        <link head-key="canonical" rel="canonical" href="https://corpius.net/incident-response-policy" />
        <meta head-key="og:title" property="og:title" content="Incident Response Policy | CORPIUS" />
        <meta head-key="og:description" property="og:description" content="CORPIUS Incident Response Policy outlining our procedures for handling security incidents and data breaches." />
        <meta head-key="og:url" property="og:url" content="https://corpius.net/incident-response-policy" />
        <meta head-key="twitter:title" name="twitter:title" content="Incident Response Policy | CORPIUS" />
        <meta head-key="twitter:description" name="twitter:description" content="CORPIUS Incident Response Policy outlining our procedures for handling security incidents and data breaches." />
    </Head>

    <MarketingLayout>
        <!-- Hero -->
        <section class="bg-gradient-to-br from-navy-900 via-blue-900 to-navy-900 py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl lg:text-5xl font-bold text-white mb-4">
                    Incident Response <span class="text-yellow-400">Policy</span>
                </h1>
                <p class="text-gray-300 text-lg">Last Updated: March 2, 2026</p>
            </div>
        </section>

        <!-- Content -->
        <section class="py-16" style="background-color: #0b1e33;">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="prose-legal">

                    <div class="legal-section">
                        <p class="legal-p">This Incident Response Policy ("Policy") establishes the required procedures and responsibilities for identifying, reporting, analyzing, containing, mitigating, documenting, and resolving security incidents involving CORPIUS systems and data.</p>
                        <p class="legal-p">This IRP is critical for ensuring compliance with U.S. regulatory requirements, state-level data-breach statutes, and international standards (GDPR, UK GDPR, CCPA/CPRA).</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">1. Purpose</h2>
                        <p class="legal-p">The purpose of this Incident Response Policy is to:</p>
                        <ul class="legal-ul">
                            <li>Protect CORPIUS systems, infrastructure, and client data from security threats</li>
                            <li>Ensure a fast, structured response to security events</li>
                            <li>Reduce risk and potential financial, legal, and operational damage</li>
                            <li>Establish accountability, documentation, and forensic accuracy</li>
                            <li>Comply with U.S. and international data-protection regulations</li>
                        </ul>
                        <p class="legal-p">This policy applies to all employees, contractors, consultants, and authorized users.</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">2. Definitions</h2>
                        <h3 class="legal-h3">2.1 Security Incident</h3>
                        <p class="legal-p">Any event — confirmed or suspected — that threatens the confidentiality, integrity, availability, authenticity, or accuracy of CORPIUS systems, networks, data, or client information.</p>
                        <h3 class="legal-h3">2.2 Examples of Incidents</h3>
                        <ul class="legal-ul">
                            <li>Unauthorized access attempts or compromised credentials</li>
                            <li>Suspicious logins or abnormal behavior</li>
                            <li>Malware, phishing, ransomware</li>
                            <li>Data exfiltration or data leak</li>
                            <li>Loss or theft of devices</li>
                            <li>Corrupted corporate/tax documents</li>
                            <li>Attempts to access information of other clients</li>
                            <li>System outages affecting document uploads</li>
                            <li>Third-party vendor breaches</li>
                            <li>Fraudulent or suspicious client uploads</li>
                            <li>Abuse of AI or automated tools</li>
                            <li>Failed or unexpected changes in state or IRS filings</li>
                        </ul>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">3. Incident Response Team (IRT)</h2>
                        <p class="legal-p">CORPIUS maintains an internal Incident Response Team responsible for all IRP procedures.</p>
                        <h3 class="legal-h3">3.1 Roles and Responsibilities</h3>
                        <div class="space-y-4 mb-4">
                            <div class="legal-principle-card">
                                <h4 class="text-yellow-400 font-bold mb-1">Incident Response Lead (IRL)</h4>
                                <ul class="legal-ul text-sm">
                                    <li>Coordinates all incident response actions</li>
                                    <li>Authorizes escalation and containment steps</li>
                                    <li>Approves external notifications and regulatory filings</li>
                                </ul>
                            </div>
                            <div class="legal-principle-card">
                                <h4 class="text-yellow-400 font-bold mb-1">Security Operations</h4>
                                <ul class="legal-ul text-sm">
                                    <li>Detects, monitors, and investigates technical aspects of incidents</li>
                                    <li>Implements containment and remediation measures</li>
                                    <li>Preserves forensic evidence and maintains incident logs</li>
                                </ul>
                            </div>
                            <div class="legal-principle-card">
                                <h4 class="text-yellow-400 font-bold mb-1">Legal &amp; Compliance</h4>
                                <ul class="legal-ul text-sm">
                                    <li>Assesses regulatory notification obligations (GDPR, CCPA, state breach laws)</li>
                                    <li>Coordinates with external legal counsel when required</li>
                                    <li>Manages documentation for regulatory and litigation purposes</li>
                                </ul>
                            </div>
                            <div class="legal-principle-card">
                                <h4 class="text-yellow-400 font-bold mb-1">Communications</h4>
                                <ul class="legal-ul text-sm">
                                    <li>Manages internal and external communications during an incident</li>
                                    <li>Prepares client and regulatory notifications</li>
                                    <li>Handles media and public relations if necessary</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">4. Incident Classification</h2>
                        <div class="overflow-x-auto mb-4">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-blue-700">
                                        <th class="py-3 px-4 text-yellow-400 font-bold">Severity</th>
                                        <th class="py-3 px-4 text-yellow-400 font-bold">Description</th>
                                        <th class="py-3 px-4 text-yellow-400 font-bold">Response Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-blue-900">
                                        <td class="py-3 px-4 text-red-400 font-semibold">Critical</td>
                                        <td class="py-3 px-4 text-gray-300">Active breach, data exfiltration, ransomware, major system compromise</td>
                                        <td class="py-3 px-4 text-gray-300">Immediate (within 1 hour)</td>
                                    </tr>
                                    <tr class="border-b border-blue-900">
                                        <td class="py-3 px-4 text-orange-400 font-semibold">High</td>
                                        <td class="py-3 px-4 text-gray-300">Unauthorized access, compromised credentials, suspected data exposure</td>
                                        <td class="py-3 px-4 text-gray-300">Within 4 hours</td>
                                    </tr>
                                    <tr class="border-b border-blue-900">
                                        <td class="py-3 px-4 text-yellow-400 font-semibold">Medium</td>
                                        <td class="py-3 px-4 text-gray-300">Suspicious activity, policy violations, isolated security events</td>
                                        <td class="py-3 px-4 text-gray-300">Within 24 hours</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 text-green-400 font-semibold">Low</td>
                                        <td class="py-3 px-4 text-gray-300">Minor anomalies, failed login attempts, non-critical system alerts</td>
                                        <td class="py-3 px-4 text-gray-300">Within 72 hours</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">5. Incident Response Phases</h2>

                        <h3 class="legal-h3">Phase 1: Detection &amp; Identification</h3>
                        <ul class="legal-ul">
                            <li>Continuous monitoring systems detect anomalies and potential incidents</li>
                            <li>Employees report suspected incidents to the IRT immediately</li>
                            <li>Initial assessment to classify severity and scope</li>
                            <li>Document time of detection, source, and initial indicators</li>
                        </ul>

                        <h3 class="legal-h3">Phase 2: Containment</h3>
                        <ul class="legal-ul">
                            <li>Isolate affected systems to prevent further damage or data loss</li>
                            <li>Revoke compromised credentials and access tokens</li>
                            <li>Block malicious IP addresses or traffic sources</li>
                            <li>Preserve all evidence and logs for forensic analysis</li>
                            <li>Notify IRT leadership and activate full response procedures</li>
                        </ul>

                        <h3 class="legal-h3">Phase 3: Eradication</h3>
                        <ul class="legal-ul">
                            <li>Identify and eliminate root cause of the incident</li>
                            <li>Remove malware, unauthorized access points, or compromised components</li>
                            <li>Patch vulnerabilities and update security controls</li>
                            <li>Perform integrity checks on affected systems and data</li>
                        </ul>

                        <h3 class="legal-h3">Phase 4: Recovery</h3>
                        <ul class="legal-ul">
                            <li>Restore affected systems from secure, verified backups</li>
                            <li>Test restored systems before returning to production</li>
                            <li>Monitor for signs of re-infection or recurring issues</li>
                            <li>Verify data integrity and service functionality</li>
                        </ul>

                        <h3 class="legal-h3">Phase 5: Post-Incident Review</h3>
                        <ul class="legal-ul">
                            <li>Document full timeline, actions taken, and outcomes</li>
                            <li>Identify gaps in security controls and update procedures</li>
                            <li>Conduct lessons-learned review with all stakeholders</li>
                            <li>Update this IRP and security policies as needed</li>
                        </ul>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">6. Notification &amp; Regulatory Obligations</h2>
                        <h3 class="legal-h3">6.1 Client Notification</h3>
                        <p class="legal-p">If a breach involves client personal data, affected clients will be notified as required by law, including the nature of the breach, data affected, steps taken, and recommended client actions.</p>
                        <h3 class="legal-h3">6.2 Regulatory Notification</h3>
                        <ul class="legal-ul">
                            <li><strong>GDPR/UK GDPR:</strong> Data protection authorities notified within 72 hours of discovery</li>
                            <li><strong>CCPA/CPRA:</strong> Compliance with California data breach notification requirements</li>
                            <li><strong>State breach laws:</strong> Notification within legally mandated timeframes for all applicable U.S. states</li>
                            <li><strong>IRS:</strong> Notification of incidents involving tax data per applicable IRS guidance</li>
                        </ul>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">7. Evidence Preservation</h2>
                        <p class="legal-p">All incident-related evidence must be preserved, including:</p>
                        <ul class="legal-ul">
                            <li>System logs, access logs, and audit trails</li>
                            <li>Network traffic captures and firewall logs</li>
                            <li>Email records and communication logs related to the incident</li>
                            <li>Forensic images of affected systems</li>
                            <li>Chain of custody documentation for all evidence</li>
                        </ul>
                        <p class="legal-p">Evidence must not be altered, deleted, or modified during or after an investigation.</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">8. Training &amp; Testing</h2>
                        <p class="legal-p">To ensure effective incident response, CORPIUS:</p>
                        <ul class="legal-ul">
                            <li>Conducts regular IRP training for all relevant staff</li>
                            <li>Performs tabletop exercises simulating various incident scenarios</li>
                            <li>Tests backup restoration and recovery procedures quarterly</li>
                            <li>Reviews and updates this Policy at least annually</li>
                        </ul>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">9. Reporting a Security Concern</h2>
                        <p class="legal-p">If you discover or suspect a security vulnerability or incident related to CORPIUS systems, please report it immediately:</p>
                        <div class="legal-contact-box">
                            <p class="text-white font-semibold mb-2">CORPIUS — Security Incident Reporting</p>
                            <p>Email: <a href="mailto:admin@corpius.net" class="legal-link">admin@corpius.net</a></p>
                            <p>Website: <a href="https://corpius.net" class="legal-link">https://corpius.net</a></p>
                            <p class="text-sm text-gray-400 mt-2">For critical security incidents, please mark your email subject as "SECURITY INCIDENT — URGENT"</p>
                        </div>
                        <p class="legal-p">We take all security reports seriously and will respond promptly. We do not take legal action against good-faith security researchers who follow responsible disclosure practices.</p>
                    </div>

                    <div class="legal-section">
                        <h2 class="legal-h2">10. Policy Review &amp; Updates</h2>
                        <p class="legal-p">This Incident Response Policy is reviewed and updated at least annually or following any significant incident, change in regulatory requirements, or major update to CORPIUS systems or infrastructure. Updates become effective immediately upon posting.</p>
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
