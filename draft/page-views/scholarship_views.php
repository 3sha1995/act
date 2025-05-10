<!-- New About Section -->
<section class="scholarship_about_section">
    <div class="scholarship_about_container">
        <div class="scholarship_about_header">
            <h2 class="scholarship_about_title">SCHOLARSHIP AND FINANCIAL AID</h2>
            <div class="scholarship_about_divider"></div>
        </div>
        
        <div class="scholarship_about_content">
            <div class="scholarship_about_image">
                <img src="../imgs/cte.jpg" alt="WMSU Health Center">
            </div>
            
            <div class="scholarship_about_text">
                <p class="scholarship_about_description">
                    The Western Mindanao State University Health Center is a comprehensive healthcare facility 
                    designed to meet the medical needs of students, faculty, staff, and the university community. 
                    Our team of qualified healthcare professionals provides a wide range of services including 
                    general check-ups, first aid, health consultations, and preventive care.
                </p>
                <p class="scholarship_about_description">
                    We are committed to creating a healthy campus environment through regular health awareness 
                    programs, wellness initiatives, and emergency response services. Our modern facilities are 
                    equipped with essential medical equipment to ensure prompt and effective healthcare delivery.
                </p>
           
            </div>
        </div>
    </div>
</section>

<section class="scholarship_mv_wrapper">
    <h2 class="scholarship_mv_main_title">MISSION AND VISION</h2>
    <div class="scholarship_about_divider"></div>
<section class="scholarship_mv_container">
    <div class="scholarship_mv_box_mission" onclick="expandSection(this, 'mission')">
        <img src="../imgs/cte.jpg" alt="WMSU Mission">
        <div class="scholarship_mv_overlay_mission"></div>
        <div class="scholarship_mv_content">
            <h2 class="scholarship_mv_title">MISSION</h2>
            <div class="scholarship_mv_show_more">
                <span class="show_more_icon">+</span>
                <span class="show_more_text">SHOW MORE</span>
            </div>
            <div class="scholarship_mv_full_content">
                <p>The Scholarship and Financial Assistance Office is dedicated to providing equal opportunities for quality education by administering financial support programs, identifying and assisting deserving students, and ensuring the efficient and transparent management of scholarship resources to promote academic excellence and social equity.</p>
            </div>
        </div>
    </div>
    
    <div class="scholarship_mv_box_vision" onclick="expandSection(this, 'vision')">
        <img src="../imgs/cte-field.png" alt="WMSU Vision">
        <div class="scholarship_mv_overlay_vision"></div>
        <div class="scholarship_mv_content">
            <h2 class="scholarship_mv_title">VISION</h2>
            <div class="scholarship_mv_show_more">
                <span class="show_more_icon">+</span>
                <span class="show_more_text">SHOW MORE</span>
            </div>
            <div class="scholarship_mv_full_content">
                <p>To be a leading scholarship management office recognized for expanding educational access, fostering academic achievement, and developing innovative financial support programs that transform the lives of students and contribute to the university's mission of excellence and inclusion in higher education.</p>
            </div>
        </div>
    </div>
</section>
</section>

<script>
   function expandSection(element, section) {
    const container = document.querySelector('.scholarship_mv_container');
    const boxes = document.querySelectorAll('.scholarship_mv_box_mission, .scholarship_mv_box_vision');
    
    boxes.forEach(box => {
        if (box === element) {
            box.classList.toggle('expanded');
            if (box.classList.contains('expanded')) {
                container.classList.add('has-expanded');
            } else {
                container.classList.remove('has-expanded');
            }
        } else {
            box.classList.remove('expanded');
        }
    });
}
</script>

<section class="scholarship_objective_section">
    <div class="scholarship_objective_container">
        <h2 class="scholarship_objective_title">General Objectives</h2>
        <div class="scholarship_objective_content">
            <div class="scholarship_objective_list">
                <div class="scholarship_objective_item">
                    <div class="scholarship_objective_icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="scholarship_objective_text">
                        <h3>Access to Education</h3>
                        <p>Ensure equal access to quality education for all qualified students regardless of financial status</p>
                    </div>
                </div>
                <div class="scholarship_objective_item">
                    <div class="scholarship_objective_icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="scholarship_objective_text">
                        <h3>Academic Excellence</h3>
                        <p>Promote and reward academic excellence among students</p>
                    </div>
                </div>
                <div class="scholarship_objective_item">
                    <div class="scholarship_objective_icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="scholarship_objective_text">
                        <h3>Student Development</h3>
                        <p>Support the holistic development of students through various scholarship programs</p>
                    </div>
                </div>
                <div class="scholarship_objective_item">
                    <div class="scholarship_objective_icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="scholarship_objective_text">
                        <h3>Community Impact</h3>
                        <p>Contribute to the development of the community by producing competent graduates</p>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>


<section class="scholarship_services_section">
  <div class="scholarship_services_container">
    <h2 class="scholarship_services_title">SCHOLARSHIPS OFFERED</h2>

    <!-- DOST Scholarship -->
    <div class="scholarship_services_item">
      <button class="scholarship_services_toggle">Department of Science and Technology (DOST)</button>
      <div class="scholarship_services_content">
        <p>
          The Department of Science and Technology aims to promote the development of science and technology human resources in the Philippines by providing scholarships, grants, and incentives to deserving science students.
        </p>
        <h4>Eligibility:</h4>
        <ul>
          <li>Filipino citizen</li>
          <li>Good moral character</li>
          <li>Belongs to STEM strand</li>
          <li>Pass the DOST-SEI examination</li>
        </ul>
        <h4>What it Offers:</h4>
        <ul>
          <li>₱40,000/year for tuition and other fees</li>
          <li>₱10,000/year book allowance</li>
          <li>₱7,000/month living allowance</li>
          <li>Clothing allowance</li>
        </ul>
        <a href="forms/dost_form.pdf" class="scholarship_services_download" download>📄 Download Application Form</a>
      </div>
    </div>

    <!-- CHED TES -->
    <div class="scholarship_services_item">
      <button class="scholarship_services_toggle">CHED TES Tulong Dunong Program</button>
      <div class="scholarship_services_content">
        <p>
          The Tulong Dunong Program is a government scholarship under CHED that helps financially needy students with their educational expenses.
        </p>
        <h4>Eligibility:</h4>
        <ul>
          <li>Enrolled in undergraduate programs</li>
          <li>With financial need and academic merit</li>
        </ul>
        <h4>What it Offers:</h4>
        <ul>
          <li>Up to ₱7,500 per semester for school-related expenses</li>
        </ul>
        <a href="forms/ched_tes_form.pdf" class="scholarship_services_download" download>📄 Download Application Form</a>
      </div>
    </div>

    <!-- CHED Tertiary -->
    <div class="scholarship_services_item">
      <button class="scholarship_services_toggle">CHED Tertiary Education System</button>
      <div class="scholarship_services_content">
        <p>
          A program designed to ensure access to quality tertiary education through free tuition and other subsidies in State Universities and Colleges.
        </p>
        <h4>Eligibility:</h4>
        <ul>
          <li>Filipino undergraduate students enrolled in SUCs/LUCs</li>
          <li>Must pass admission requirements</li>
        </ul>
        <h4>What it Offers:</h4>
        <ul>
          <li>Free tuition and other school fees</li>
          <li>Stipends and allowances for qualified students</li>
        </ul>
      </div>
    </div>

    <!-- PVAO -->
    <div class="scholarship_services_item">
      <button class="scholarship_services_toggle">Philippine Veterans Affairs Office (PVAO)</button>
      <div class="scholarship_services_content">
        <p>
          Provides educational benefits to qualified dependents of veterans as a way of honoring their service.
        </p>
        <h4>Eligibility:</h4>
        <ul>
          <li>Children or dependents of Filipino veterans</li>
          <li>Must be enrolled in a recognized educational institution</li>
        </ul>
        <h4>What it Offers:</h4>
        <ul>
          <li>Educational assistance for tuition and allowances</li>
        </ul>
        <a href="forms/pvao_form.pdf" class="scholarship_services_download" download>📄 Download Application Form</a>
      </div>
    </div>
  </div>
</section>

<section class="financial_services_section">
  <div class="financial_services_container">
    <h2 class="financial_services_title">FINANCIAL AID OFFERED</h2>

    <!-- Student Assistance -->
    <div class="financial_services_item">
      <button class="financial_services_toggle">Student Assistance Program</button>
      <div class="financial_services_content">
        <p>
          The Student Assistance Program provides financial support to students facing economic hardships to help them continue their education.
        </p>
        <h4>Eligibility:</h4>
        <ul>
          <li>Currently enrolled students</li>
          <li>Demonstrated financial need</li>
          <li>Good academic standing</li>
          <li>No other financial assistance</li>
        </ul>
        <h4>What it Offers:</h4>
        <ul>
          <li>Monthly stipend for educational expenses</li>
          <li>Book and supplies allowance</li>
          <li>Transportation assistance</li>
          <li>Emergency financial aid</li>
        </ul>
        <a href="forms/student_assistance_form.pdf" class="financial_services_download" download>📄 Download Application Form</a>
      </div>
    </div>

    <!-- Work-Study Program -->
    <div class="financial_services_item">
      <button class="financial_services_toggle">Work-Study Program</button>
      <div class="financial_services_content">
        <p>
          The Work-Study Program offers part-time employment opportunities to students within the university while maintaining their studies.
        </p>
        <h4>Eligibility:</h4>
        <ul>
          <li>Full-time enrolled students</li>
          <li>Minimum GPA requirement</li>
          <li>Available to work 10-15 hours per week</li>
        </ul>
        <h4>What it Offers:</h4>
        <ul>
          <li>Hourly compensation</li>
          <li>Flexible work schedule</li>
          <li>Valuable work experience</li>
        </ul>
        <a href="forms/work_study_form.pdf" class="financial_services_download" download>📄 Download Application Form</a>
      </div>
    </div>

    <!-- Emergency Fund -->
    <div class="financial_services_item">
      <button class="financial_services_toggle">Emergency Financial Aid</button>
      <div class="financial_services_content">
        <p>
          Emergency Financial Aid provides immediate assistance to students facing unexpected financial crises that may affect their education.
        </p>
        <h4>Eligibility:</h4>
        <ul>
          <li>Currently enrolled students</li>
          <li>Unexpected financial emergency</li>
          <li>No other immediate financial resources</li>
        </ul>
        <h4>What it Offers:</h4>
        <ul>
          <li>One-time emergency grant</li>
          <li>Crisis counseling and support</li>
          <li>Financial planning assistance</li>
        </ul>
      </div>
    </div>

    <!-- Student Loan Program -->
    <div class="financial_services_item">
      <button class="financial_services_toggle">Student Loan Program</button>
      <div class="financial_services_content">
        <p>
          The Student Loan Program offers low-interest loans to help students manage their educational expenses.
        </p>
        <h4>Eligibility:</h4>
        <ul>
          <li>Enrolled students in good standing</li>
          <li>Demonstrated financial need</li>
          <li>With capable co-borrower</li>
        </ul>
        <h4>What it Offers:</h4>
        <ul>
          <li>Low-interest educational loans</li>
          <li>Flexible repayment terms</li>
          <li>Financial counseling services</li>
        </ul>
        <a href="forms/student_loan_form.pdf" class="financial_services_download" download>📄 Download Application Form</a>
      </div>
    </div>
  </div>
</section>

<script>
document.querySelectorAll('.scholarship_services_toggle, .financial_services_toggle').forEach(button => {
    button.addEventListener('click', () => {
        const content = button.nextElementSibling;
        const isVisible = content.style.display === 'block';
        
        // Close all content sections in both scholarship and financial services
        document.querySelectorAll('.scholarship_services_content, .financial_services_content').forEach(c => c.style.display = 'none');
        
        if (!isVisible) {
            content.style.display = 'block';
        }
    });
});
</script>

<section class="combined_apply_container">
    <div class="combined_apply_content">
        <!-- Scholarship Process -->
        <div class="scholarship_apply_process">
            <h2 class="scholarship_apply_title">How to Apply for Scholarships</h2>
            <ul class="scholarship_apply_list">
                <li class="scholarship_apply_item">
                    <span class="bullet">•</span>
                    <div class="scholarship_apply_text">
                        <h3>Review Requirements</h3>
                        <p>Check eligibility criteria and requirements for your chosen scholarship program.</p>
                    </div>
                </li>
                <li class="scholarship_apply_item">
                    <span class="bullet">•</span>
                    <div class="scholarship_apply_text">
                        <h3>Prepare Documents</h3>
                        <p>Gather transcripts, recommendation letters, and certificates.</p>
                    </div>
                </li>
                <li class="scholarship_apply_item">
                    <span class="bullet">•</span>
                    <div class="scholarship_apply_text">
                        <h3>Submit Application</h3>
                        <p>Complete and submit the application form with required documents.</p>
                    </div>
                </li>
                <li class="scholarship_apply_item">
                    <span class="bullet">•</span>
                    <div class="scholarship_apply_text">
                        <h3>Interview Process</h3>
                        <p>If shortlisted, attend the scholarship committee interview.</p>
                    </div>
                </li>
                <li class="scholarship_apply_item">
                    <span class="bullet">•</span>
                    <div class="scholarship_apply_text">
                        <h3>Acceptance</h3>
                        <p>Complete the scholarship acceptance process if selected.</p>
                    </div>
                </li>
            </ul>
        </div>
        
        <!-- Financial Aid Process -->
        <div class="financial_apply_process">
            <h2 class="financial_apply_title">How to Apply for Financial Aid</h2>
            <ul class="financial_apply_list">
                <li class="financial_apply_item">
                    <span class="bullet">•</span>
                    <div class="financial_apply_text">
                        <h3>Financial Assessment</h3>
                        <p>Complete the initial financial need assessment form.</p>
                    </div>
                </li>
                <li class="financial_apply_item">
                    <span class="bullet">•</span>
                    <div class="financial_apply_text">
                        <h3>Document Submission</h3>
                        <p>Submit proof of income and required financial documents.</p>
                    </div>
                </li>
                <li class="financial_apply_item">
                    <span class="bullet">•</span>
                    <div class="financial_apply_text">
                        <h3>Counseling Session</h3>
                        <p>Attend financial counseling to discuss aid options.</p>
                    </div>
                </li>
                <li class="financial_apply_item">
                    <span class="bullet">•</span>
                    <div class="financial_apply_text">
                        <h3>Aid Package Review</h3>
                        <p>Review and accept the proposed financial aid package.</p>
                    </div>
                </li>
                <li class="financial_apply_item">
                    <span class="bullet">•</span>
                    <div class="financial_apply_text">
                        <h3>Finalization</h3>
                        <p>Complete paperwork and receive aid disbursement schedule.</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!-- Combined Downloadable Forms Section -->
    <div class="combined_forms_section">
        <h2 class="combined_forms_title">Required Forms & Documents</h2>
        <div class="forms_list">
            <a href="#" class="form_item">
                <div class="form_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                    </svg>
                </div>
                <div class="form_text">
                    <h4>Scholarship Application Form</h4>
                    <p>General application form for all scholarships</p>
                </div>
            </a>
            <a href="#" class="form_item">
                <div class="form_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                    </svg>
                </div>
                <div class="form_text">
                    <h4>Financial Aid Application</h4>
                    <p>Primary application for financial assistance</p>
                </div>
            </a>
            <a href="#" class="form_item">
                <div class="form_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                    </svg>
                </div>
                <div class="form_text">
                    <h4>Recommendation Letter Template</h4>
                    <p>Format for academic recommendations</p>
                </div>
            </a>
            <a href="#" class="form_item">
                <div class="form_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                    </svg>
                </div>
                <div class="form_text">
                    <h4>Income Declaration Form</h4>
                    <p>Family income and expenses statement</p>
                </div>
            </a>
            <a href="#" class="form_item">
                <div class="form_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                    </svg>
                </div>
                <div class="form_text">
                    <h4>Grade Certification Form</h4>
                    <p>Academic performance verification</p>
                </div>
            </a>
            <a href="#" class="form_item">
                <div class="form_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                    </svg>
                </div>
                <div class="form_text">
                    <h4>Asset Declaration Form</h4>
                    <p>Statement of assets and properties</p>
                </div>
            </a>
        </div>
    </div>
</section>

<section class="scholarship_financial_officer_section">
    <h2 class="scholarship_financial_officer_title">Meet Our Officers</h2>
    <div class="scholarship_financial_officer_grid">
        <!-- Officer 1 -->
        <div class="scholarship_financial_officer_card">
            <div class="scholarship_financial_officer_image_wrapper">
                <img src="../imgs/officers/officer1.jpg" alt="Officer 1" class="scholarship_financial_officer_image">
            </div>
            <div class="scholarship_financial_officer_info">
                <h3 class="scholarship_financial_officer_name">John Doe</h3>
                <p class="scholarship_financial_officer_position">Scholarship Program Director</p>
            </div>
        </div>

        <!-- Officer 2 -->
        <div class="scholarship_financial_officer_card">
            <div class="scholarship_financial_officer_image_wrapper">
                <img src="../imgs/officers/officer2.jpg" alt="Officer 2" class="scholarship_financial_officer_image">
            </div>
            <div class="scholarship_financial_officer_info">
                <h3 class="scholarship_financial_officer_name">Jane Smith</h3>
                <p class="scholarship_financial_officer_position">Financial Aid Coordinator</p>
            </div>
        </div>

        <!-- Officer 3 -->
        <div class="scholarship_financial_officer_card">
            <div class="scholarship_financial_officer_image_wrapper">
                <img src="../imgs/officers/officer3.jpg" alt="Officer 3" class="scholarship_financial_officer_image">
            </div>
            <div class="scholarship_financial_officer_info">
                <h3 class="scholarship_financial_officer_name">Michael Lee</h3>
                <p class="scholarship_financial_officer_position">Student Aid Officer</p>
            </div>
        </div>

        <!-- Officer 4 -->
        <div class="scholarship_financial_officer_card">
            <div class="scholarship_financial_officer_image_wrapper">
                <img src="../imgs/cte.jpg" alt="Officer 4" class="scholarship_financial_officer_image">
            </div>
            <div class="scholarship_financial_officer_info">
                <h3 class="scholarship_financial_officer_name">Sarah Johnson</h3>
                <p class="scholarship_financial_officer_position">Grant Programs Lead</p>
            </div>
        </div>
    </div>
</section>


<section class="scholarship_logo_section">
    <div class="scholarship_logo_container">
        <img src="../imgs/salogo1.png" alt="Scholarship Services Logo" class="scholarship_logo">
        <h2>Scholarship and Financial Aid</h2>
        <p>Western Mindanao State University</p>
    </div>
</section>

<section class="scholarship_financial_contact_section">
    <div class="scholarship_financial_contact_container">
        <h2 class="scholarship_financial_contact_header">Get in Touch</h2>
        <div class="contact_grid">
            <div class="contact_info">
                <div class="contact_item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    <div class="contact_text">
                        <h3>Phone</h3>
                        <p>(062) 991-6446</p>
                    </div>
                </div>
                <div class="contact_item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <div class="contact_text">
                        <h3>Email</h3>
                        <p>scholarship@wmsu.edu.ph</p>
                    </div>
                </div>
                <div class="contact_item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <div class="contact_text">
                        <h3>Location</h3>
                        <p>Normal Road, Baliwasan, Zamboanga City</p>
                    </div>
                </div>
            </div>
            <div class="social_links">
                <h3>Follow Us</h3>
                <div class="social_icons">
                    <a href="#" target="_blank" class="social_icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.77 7.46H14.5v-1.9c0-.9.6-1.1 1-1.1h3V.5h-4.33C10.24.5 9.5 3.44 9.5 5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4z"/>
                        </svg>
                        <span>WMSU Scholarship and Financial Aid</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

