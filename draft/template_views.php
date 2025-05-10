<!-- New About Section -->
<section class="student_affairs_about_section">
    <div class="student_affairs_about_container">
   
    <div class="student_affairs_about_header scroll-fade">
    <div class="student_affairs_about_ontop_title">ABOUT US</div>
    <h2 class="student_affairs_about_title">STUDENT AFFAIRS</h2>
    <div class="student_affair_about_divider"></div>
    </div>
   
     
        <div class="student_affairs_about_content scroll-fade">
            <div class="student_affairs_about_image">
                <img src="../imgs/cte.jpg" alt="WMSU Health Center">
            </div>
            
            <div class="student_affairs_about_text">
                <p class="student_affairs_about_description">
                    The Western Mindanao State University Health Center is a comprehensive healthcare facility 
                    designed to meet the medical needs of students, faculty, staff, and the university community. 
                    Our team of qualified healthcare professionals provides a wide range of services including 
                    general check-ups, first aid, health consultations, and preventive care.  
                    
                    We are committed to creating a healthy campus environment through regular health awareness 
                    programs, wellness initiatives, and emergency response services. Our modern facilities are 
                    equipped with essential medical equipment to ensure prompt and effective healthcare delivery.
                </p>
                
               
            </div>
        </div>
    </div>
    </div>
</section>

<script>
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.2
    });

    document.querySelectorAll('.scroll-fade').forEach(el => {
        observer.observe(el);
    });
</script>



    <section class="student_affair_mv_wrapper">
        <h2 class="student_affair_mv_main_title">MISSION AND VISION</h2>
        <div class="student_affair_mv_divider"></div>
    <section class="student_affair_mv_container">
        <div class="student_affair_mv_box_mission" onclick="expandSection(this, 'mission')">
            <img src="../imgs/cte.jpg" alt="WMSU Mission">
            <div class="student_affair_mv_overlay_mission"></div>
            <div class="student_affair_mv_content">
                <h2 class="student_affair_mv_title">MISSION</h2>
                <div class="student_affair_mv_show_more">

                    <span class="show_more_text">SHOW MORE</span>
                </div>
                <div class="student_affair_mv_full_content">
                    <p> We are committed to creating a healthy campus environment through regular health awareness 
                    programs, wellness initiatives, and emergency response services. Our modern facilities are 
                    equipped with essential medical equipment to ensure prompt and effective healthcare delivery.</p>
                    
                </div>
            </div>
        </div>
        
        <div class="student_affair_mv_box_vision" onclick="expandSection(this, 'vision')">
            <img src="../imgs/cte-field.png" alt="WMSU Vision">
            <div class="student_affair_mv_overlay_vision"></div>
            <div class="student_affair_mv_content">
                <h2 class="student_affair_mv_title">VISION</h2>
                <div class="student_affair_mv_show_more">
                    <span class="show_more_icon">+</span>
                    <span class="show_more_text">SHOW MORE</span>
                </div>
                <div class="student_affair_mv_full_content">
                    <p> We are committed to creating a healthy campus environment through regular health awareness 
                    programs, wellness initiatives, and emergency response services. Our modern facilities are 
                    equipped with essential medical equipment to ensure prompt and effective healthcare delivery..</p>
                    <p>We envision Western Mindanao State University to be:</p>
                    
                </div>
            </div>
        </div>
    </section>
    </section>

    <section class="student_affairs_objectives_section">
    <div class="student_affairs_objectives_container">
        <h2 class="student_affairs_objectives_title">OUR OBJECTIVES</h2>
        <div class="student_affairs_objectives_divider"></div>
        
        <div class="student_affairs_objectives_content">
            <p class="student_affairs_objectives_description">
                The University Health Services of Western Mindanao State University is committed to promoting health and wellness 
                within the university community through the following objectives:
            </p>
            
            <ul class="student_affairs_objectives_list">
                <li>To provide accessible, high-quality healthcare services to all students, faculty, and staff</li>
                <li>To promote health awareness and preventive healthcare through educational programs and campaigns</li>
                <li>To offer timely medical consultation, treatment, and referral services for various health concerns</li>
                <li>To maintain a safe and healthy campus environment through health surveillance and infection control measures</li>
                <li>To collaborate with academic departments and other university units in promoting holistic wellness</li>
                <li>To continuously improve healthcare delivery through staff development and facility enhancement</li>
            </ul>
        </div>
    </div>
</section>

<!-- Health Functions Section -->
<section class="student_affairs_functions_section">
    <div class="student_affairs_functions_container">
        <h2 class="student_affairs_functions_title">KEY FUNCTIONS</h2>
        <div class="student_affairs_functions_divider"></div>
        
        <div class="student_affairs_functions_content">
            <p class="student_affairs_functions_description">
                The University Health Services performs the following key functions to ensure the health and well-being 
                of the entire WMSU community:
            </p>
            
          
        </div>
    </div>
</section>


<section class="student_affairs_services_section">
    <div class="student_affairs_services_container">
        <div class="student_affairs_services_header">
            <h2 class="student_affairs_services_title">Our Services</h2>
            <div class="student_affairs_services_divider"></div>
            <p class="student_affairs_services_description">We provide a range of healthcare services to support the well-being of our university community.</p>
        </div>
    <div class="student_affairs_services_grid">
        <!-- Service Card 1 -->
        <div class="student_affairs_service_card">
                <div class="student_affairs_service_icon">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <div class="student_affairs_service_content">
                    <h3 class="student_affairs_service_title">Medical Consultation</h3>
            <p class="student_affairs_service_description">
                Professional medical care and health assessment for students and staff.
            </p>
                   
                </div>
        </div>

        <!-- Service Card 2 -->
        <div class="student_affairs_service_card">
                <div class="student_affairs_service_icon">
                    <i class="fas fa-tooth"></i>
                </div>
                <div class="student_affairs_service_content">
            <h3 class="student_affairs_service_title">Dental Services</h3>
            <p class="student_affairs_service_description">
                Comprehensive dental care and oral health services.
            </p>
                   
                </div>
        </div>

        <!-- Service Card 3 -->
        <div class="student_affairs_service_card">
                <div class="student_affairs_service_icon">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="student_affairs_service_content">
            <h3 class="student_affairs_service_title">Laboratory Services</h3>
            <p class="student_affairs_service_description">
                Medical tests and diagnostic procedures for health monitoring.
            </p>
                    
                </div>
            </div>
        </div>
    </div>
</section>




<section class="student_affairs_activities_section">
    <div class="student_affairs_activities_container">
        <h2 class="student_affairs_activities_title">ACTIVITIES CALENDAR</h2>
        <div class="student_affairs_activities_divider"></div>
        
        <div class="student_affairs_activities_timeline">
            <!-- Event 1: Recent -->
            <div class="student_affairs_event" data-date="2023-08-10">
                <div class="student_affairs_event_date">
                    <span class="student_affairs_event_month">AUG</span>
                    <span class="student_affairs_event_day">10</span>
                </div>
                <div class="student_affairs_event_content">
                    <div class="student_affairs_event_image">
                        <img src="../imgs/cte.jpg" alt="Freshman Orientation Seminar">
                    </div>
                    <div class="student_affairs_event_details">
                        <h3>Freshman Orientation Seminar</h3>
                        <div class="student_affairs_event_meta">
                            <span><i class="fas fa-map-marker-alt"></i> University Auditorium</span>
                            <span><i class="fas fa-clock"></i> 8:00 AM - 5:00 PM</span>
                        </div>
                        <p>Comprehensive orientation program for new students covering academic policies, campus resources, and student life.</p>
                    </div>
                </div>
            </div>
            
            <!-- Event 2: Today -->
            <div class="student_affairs_event" data-date="2023-08-15">
                <div class="student_affairs_event_date">
                    <span class="student_affairs_event_month">AUG</span>
                    <span class="student_affairs_event_day">15</span>
                </div>
                <div class="student_affairs_event_content">
                    <div class="student_affairs_event_image">
                        <img src="../imgs/cte-field.png" alt="Student Leadership Conference">
                    </div>
                    <div class="student_affairs_event_details">
                        <h3>Student Leadership Conference</h3>
                        <div class="student_affairs_event_meta">
                            <span><i class="fas fa-map-marker-alt"></i> Conference Center</span>
                            <span><i class="fas fa-clock"></i> 9:00 AM - 4:00 PM</span>
                        </div>
                        <p>Annual conference focusing on developing leadership skills among student organization officers and aspiring leaders.</p>
                       
                    </div>
                </div>
            </div>
            
            <!-- Event 3: Upcoming -->
            <div class="student_affairs_event" data-date="2023-08-20">
                <div class="student_affairs_event_date">
                    <span class="student_affairs_event_month">AUG</span>
                    <span class="student_affairs_event_day">20</span>
                </div>
                <div class="student_affairs_event_content">
                    <div class="student_affairs_event_image">
                        <img src="../imgs/cte.jpg" alt="Career Development Workshop">
                    </div>
                    <div class="student_affairs_event_details">
                        <h3>Career Development Workshop</h3>
                        <div class="student_affairs_event_meta">
                            <span><i class="fas fa-map-marker-alt"></i> Career Services Center</span>
                            <span><i class="fas fa-clock"></i> 1:00 PM - 5:00 PM</span>
                        </div>
                        <p>Workshop focused on resume building, interview skills, and job search strategies for graduating students.</p>
                     
                    </div>
                </div>
            </div>
            
            <!-- Event 4: Upcoming -->
            <div class="student_affairs_event" data-date="2023-09-05">
                <div class="student_affairs_event_date">
                    <span class="student_affairs_event_month">SEP</span>
                    <span class="student_affairs_event_day">05</span>
                </div>
                <div class="student_affairs_event_content">
                    <div class="student_affairs_event_image">
                        <img src="../imgs/cte-field.png" alt="Wellness Week">
                    </div>
                    <div class="student_affairs_event_details">
                        <h3>Wellness Week</h3>
                        <div class="student_affairs_event_meta">
                            <span><i class="fas fa-map-marker-alt"></i> Various Campus Locations</span>
                            <span><i class="fas fa-calendar-week"></i> September 5-9, 2023</span>
                        </div>
                        <p>Week-long event featuring health screenings, fitness activities, mental health workshops, and nutritional counseling.</p>
                       
                    </div>
                </div>
            </div>
            
            <!-- Event 5: Recent -->
            <div class="student_affairs_event" data-date="2023-08-05">
                <div class="student_affairs_event_date">
                    <span class="student_affairs_event_month">AUG</span>
                    <span class="student_affairs_event_day">05</span>
                </div>
                <div class="student_affairs_event_content">
                    <div class="student_affairs_event_image">
                        <img src="../imgs/ocho.png" alt="Cultural Diversity Celebration">
                    </div>
                    <div class="student_affairs_event_details">
                        <h3>Cultural Diversity Celebration</h3>
                        <div class="student_affairs_event_meta">
                            <span><i class="fas fa-map-marker-alt"></i> University Plaza</span>
                            <span><i class="fas fa-clock"></i> 11:00 AM - 7:00 PM</span>
                        </div>
                        <p>Annual event showcasing cultural performances, international cuisine, and interactive displays representing diverse cultures.</p>
                       
                    </div>
                </div>
            </div>
        </div>

        <div class="student_affairs_activities_footer">
            <a href="#" class="view_all_activities">View Complete Calendar <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<section class="student_affairs_facilities_section">
    <div class="student_affairs_facilities_container">
        <div class="student_affairs_facilities_header">
            <h2 class="student_affairs_facilities_title">OUR FACILITIES</h2>
            <div class="student_affairs_facilities_divider"></div>
            <p class="student_affairs_facilities_description">Explore our comprehensive range of campus facilities designed to support your academic journey and personal growth.</p>
        </div>
    
        
        <div class="student_affairs_facilities_grid">
            <!-- Facility 1 -->
            <div class="facility_card" data-category="social">
                <div class="facility_image">
                    <img src="../imgs/cte.jpg" alt="Student Lounge">
                    <div class="facility_overlay">
                
                      
                    </div>
                </div>
                <div class="facility_content">
                    <h3 class="facility_title">Student Lounge</h3>
                    <p class="facility_description">A comfortable space for students to relax, socialize, and collaborate between classes. Features comfortable seating, charging stations, and refreshment area.</p>
                    <div class="facility_operating_hours">
                        <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                        <ul>
                            <li><span>Monday - Friday:</span> 7:00 AM - 9:00 PM</li>
                            <li><span>Saturday:</span> 9:00 AM - 6:00 PM</li>
                            <li><span>Sunday:</span> Closed</li>
                        </ul>
                    </div>
                    
                </div>
            </div>
            
            <!-- Facility 2 -->
            <div class="facility_card" data-category="study">
                <div class="facility_image">
                    <img src="../imgs/cte.jpg" alt="Study Center">
                    <div class="facility_overlay">
                      
                        
                    </div>
                </div>
                <div class="facility_content">
                   
                    <h3 class="facility_title">Study Center</h3>
                    <p class="facility_description">Dedicated space for quiet study and academic focus. Includes individual study carrels, group study rooms, and reference materials.</p>
                    <div class="facility_operating_hours">
                        <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                        <ul>
                            <li><span>Monday - Sunday:</span> 24 Hours</li>
                            <li><span>Holiday Hours:</span> 8:00 AM - 10:00 PM</li>
                        </ul>
                    </div>
                   
                </div>
            </div>
            
            <!-- Facility 3 -->
            <div class="facility_card" data-category="wellness">
                <div class="facility_image">
                    <img src="../imgs/cte.jpg" alt="Counseling Center">
                    <div class="facility_overlay">
                    
                       
                    </div>
                </div>
                <div class="facility_content">
                  
                    <h3 class="facility_title">Counseling Center</h3>
                    <p class="facility_description">Professional counseling services in a confidential, supportive environment. Offers individual counseling, group therapy, and wellness resources.</p>
                    <div class="facility_operating_hours">
                        <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                        <ul>
                            <li><span>Monday - Friday:</span> 8:00 AM - 5:00 PM</li>
                            <li><span>By Appointment:</span> Schedule Online</li>
                            <li><span>Crisis Line:</span> 24/7 Support</li>
                        </ul>
                    </div>
                  
                    
                </div>
            </div>
            
            <!-- Facility 4 -->
            <div class="facility_card" data-category="social">
                <div class="facility_image">
                    <img src="../imgs/cte.jpg" alt="Student Organizations Hub">
                    <div class="facility_overlay">
                
                      
                    </div>
                </div>
                <div class="facility_content">
                    <h3 class="facility_title">Student Organizations Hub</h3>
                    <p class="facility_description">Central space for student organizations to meet, plan events, and collaborate. Includes meeting rooms, storage space, and event planning resources.</p>
                    <div class="facility_operating_hours">
                        <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                        <ul>
                            <li><span>Monday - Friday:</span> 8:00 AM - 7:00 PM</li>
                            <li><span>Saturday:</span> 9:00 AM - 5:00 PM</li>
                            <li><span>Sunday:</span> Closed</li>
                        </ul>
                    </div>
                    
                </div>
            </div>
            
            <!-- Facility 5 -->
            <div class="facility_card" data-category="social">
                <div class="facility_image">
                    <img src="../imgs/cte.jpg" alt="Career Development Center">
                    <div class="facility_overlay">
                
                      
                    </div>
                </div>
                <div class="facility_content">
                    <h3 class="facility_title">Career Development Center</h3>
                    <p class="facility_description">Resources for career exploration, job searching, and professional development. Includes interview rooms, resume help desk, and career counseling offices.</p>
                    <div class="facility_operating_hours">
                        <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                        <ul>
                            <li><span>Monday - Friday:</span> 8:00 AM - 5:00 PM</li>
                            <li><span>Saturday:</span> By Appointment</li>
                            <li><span>Extended Hours:</span> During Career Fair Week</li>
                        </ul>
                    </div>
                    
                </div>
            </div>
            
            <!-- Facility 6 -->
            <div class="facility_card" data-category="wellness">
                <div class="facility_image">
                    <img src="../imgs/cte.jpg" alt="Health Services Clinic">
                    <div class="facility_overlay">
                
                      
                    </div>
                </div>
                <div class="facility_content">
                    <h3 class="facility_title">Health Services Clinic</h3>
                    <p class="facility_description">Provides basic healthcare services, medical consultations, and health education. Equipped with examination rooms, pharmacy, and treatment areas.</p>
                    <div class="facility_operating_hours">
                        <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                        <ul>
                            <li><span>Monday - Friday:</span> 7:00 AM - 6:00 PM</li>
                            <li><span>Saturday:</span> 8:00 AM - 12:00 PM</li>
                            <li><span>Emergency Services:</span> 24/7</li>
                        </ul>
                    </div>
                    
                </div>
            </div>
        </div>

        
    </div>
</section>

<section class="student_affairs_clinic_info_section">
    <div class="student_affairs_clinic_info_container">
        <div class="student_affairs_clinic_info_header">
            <h2 class="student_affairs_clinic_info_title">Health Services Information</h2>
            <div class="student_affairs_clinic_info_divider"></div>
            <p class="student_affairs_clinic_info_description">Learn about our health services application process and access important forms and resources.</p>
        </div>
        
        <div class="student_affairs_clinic_info_content">
            <!-- Left Side: Application Process -->
            <div class="clinic_process_container">
                <h3 class="clinic_process_title">How to Apply for Our Services</h3>
                <div class="clinic_process_steps">
                    <div class="process_step_item">
                        <div class="process_bullet"></div>
                        <div class="process_content">
                            <h4>Check Eligibility</h4>
                            <p>Ensure you meet the necessary requirements before applying for the service.</p>
                        </div>
                    </div>
                    <div class="process_step_item">
                        <div class="process_bullet"></div>
                        <div class="process_content">
                            <h4>Complete the Application Form</h4>
                            <p>Fill out the required application form with accurate details.</p>
                        </div>
                    </div>
                    <div class="process_step_item">
                        <div class="process_bullet"></div>
                        <div class="process_content">
                            <h4>Submit Required Documents</h4>
                            <p>Provide the necessary documents to verify your application.</p>
                        </div>
                    </div>
                    <div class="process_step_item">
                        <div class="process_bullet"></div>
                        <div class="process_content">
                            <h4>Wait for Confirmation</h4>
                            <p>Our team will review your application and notify you of the result.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Downloadable Resources -->
            <div class="clinic_downloads_container">
                <h3 class="clinic_downloads_title">Downloadable Forms</h3>
                
                <div class="clinic_downloads_list">
                    <div class="clinic_download_card">
                        <div class="download_icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="download_content">
                            <h4>Application Form</h4>
                            <p>Download the main application form</p>
                        </div>
                    </div>
                    
                    <div class="clinic_download_card">
                        <div class="download_icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="download_content">
                            <h4>Requirements Checklist</h4>
                            <p>List of required documents</p>
                        </div>
                    </div>
                    
                    <div class="clinic_download_card">
                        <div class="download_icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="download_content">
                            <h4>Medical Certificate</h4>
                            <p>Medical clearance form</p>
                        </div>
                    </div>
                    
                    <div class="clinic_download_card">
                        <div class="download_icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="download_content">
                            <h4>Waiver Form</h4>
                            <p>Liability waiver document</p>
                        </div>
                    </div>
                    
                    <div class="clinic_download_card">
                        <div class="download_icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="download_content">
                            <h4>Insurance Form</h4>
                            <p>Health insurance information form</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


    <section class="student_affairs_officer_section">
    <h2 class="student_affairs_officer_title">Meet Our Officers</h2>
    <div class="student_affairs_officer_grid">
        <!-- Officer 1 -->
        <div class="student_affairs_officer_card">
            <div class="student_affairs_officer_image_wrapper">
                <img src="../imgs/officers/officer1.jpg" alt="Officer 1" class="student_affairs_officer_image">
            </div>
            <div class="student_affairs_officer_info">
                <h3 class="student_affairs_officer_name">John Doe</h3>
                <p class="student_affairs_officer_position">Student Affairs Manager</p>
            </div>
        </div>

        <!-- Officer 2 -->
        <div class="student_affairs_officer_card">
            <div class="student_affairs_officer_image_wrapper">
                <img src="../imgs/officers/officer2.jpg" alt="Officer 2" class="student_affairs_officer_image">
            </div>
            <div class="student_affairs_officer_info">
                <h3 class="student_affairs_officer_name">Jane Smith</h3>
                <p class="student_affairs_officer_position">Events Coordinator</p>
            </div>
        </div>

        <!-- Officer 3 -->
        <div class="student_affairs_officer_card">
            <div class="student_affairs_officer_image_wrapper">
                <img src="../imgs/officers/officer3.jpg" alt="Officer 3" class="student_affairs_officer_image">
            </div>
            <div class="student_affairs_officer_info">
                <h3 class="student_affairs_officer_name">Michael Lee</h3>
                <p class="student_affairs_officer_position">Community Engagement Officer</p>
            </div>
        </div>

        <!-- Officer 4 -->
        <div class="student_affairs_officer_card">
            <div class="student_affairs_officer_image_wrapper">
                <img src="../imgs/cte.jpg" alt="Officer 4" class="student_affairs_officer_image">
            </div>
            <div class="student_affairs_officer_info">
                <h3 class="student_affairs_officer_name">Sarah Johnson</h3>
                <p class="student_affairs_officer_position">Wellness Program Lead</p>
            </div>
        </div>
    </div>
</section>

    
<section class="student_affairs_logo_section">
    <div class="student_affairs_logo_container">
        <img src="../imgs/salogo1.png" alt="Health Services Logo" class="student_affairs_logo">
        <h2>Health Services</h2>
        <p>Western Mindanao State University</p>
    </div>
</section>

<section class="student_affairs_contact_section">
    <div class="student_affairs_contact_container">
        <h2 class="student_affairs_contact_header">Get in Touch</h2>
        <div class="contact_grid">
            <div class="contact_info">
                <div class="contact_item contact_phone">
                    <img src="../imgs/org.png" alt="Phone Icon" width="24" height="24">
                    <div class="contact_text">
                        <h3>Phone</h3>
                        <p>(062) 991-6446</p>
                    </div>
                </div>
                <div class="contact_item contact_email">
                    <img src="email-icon.png" alt="Email Icon" width="24" height="24">
                    <div class="contact_text">
                        <h3>Email</h3>
                        <p>health@wmsu.edu.ph</p>
                    </div>
                </div>
                <div class="contact_item contact_location">
                    <img src="location-icon.png" alt="Location Icon" width="24" height="24">
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
                        <img src="facebook-icon.png" alt="Facebook Icon" width="24" height="24">
                        <span>WMSU Health Services</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>



    <script  src="../js/student_affairs.js"></script>
    <script>
       
    function expandSection(element, section) {
        const container = document.querySelector('.student_affair_mv_container');
        const boxes = document.querySelectorAll('.student_affair_mv_box_mission, .student_affair_mv_box_vision');
        
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
    function toggleServiceInfo(serviceId) {
    const box = document.querySelector(`#${serviceId}-info`).parentElement;
    const allBoxes = document.querySelectorAll('.student_affairs_services_box');

    // Close all other boxes before opening the current one
    allBoxes.forEach(otherBox => {
        if (otherBox !== box && otherBox.classList.contains('active')) {
            otherBox.classList.remove('active');
            otherBox.querySelector('.student_affairs_services_toggle').textContent = '+';
        }
    });

    // Toggle current box
    box.classList.toggle('active');
    const toggle = box.querySelector('.student_affairs_services_toggle');
    toggle.textContent = box.classList.contains('active') ? '×' : '+';
}

    </script>

    <!-- Add Font Awesome for social icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script src="../js/student_affairs.js"></script>
    <script>
    function expandSection(element, section) {
        const container = document.querySelector('.student_affair_mv_container');
        const boxes = document.querySelectorAll('.student_affair_mv_box_mission, .student_affair_mv_box_vision');
        
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
    
    function toggleServiceInfo(serviceId) {
        const box = document.querySelector(`#${serviceId}-info`).parentElement;
        const allBoxes = document.querySelectorAll('.student_affairs_services_box');

        // Close all other boxes before opening the current one
        allBoxes.forEach(otherBox => {
            if (otherBox !== box && otherBox.classList.contains('active')) {
                otherBox.classList.remove('active');
                otherBox.querySelector('.student_affairs_services_toggle').textContent = '+';
            }
        });

        // Toggle current box
        box.classList.toggle('active');
        const toggle = box.querySelector('.student_affairs_services_toggle');
        toggle.textContent = box.classList.contains('active') ? '×' : '+';
    }
    
    // Add animation for service cards
    document.addEventListener('DOMContentLoaded', function() {
        // Animate service cards
        const serviceCards = document.querySelectorAll('.student_affairs_service_card');
        
        function animateServiceCards() {
            serviceCards.forEach((card, index) => {
                const cardPosition = card.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;
                
                if(cardPosition < screenPosition) {
                    setTimeout(() => {
                        card.classList.add('animated');
                    }, index * 150); // Stagger animation
                }
            });
        }
        
        // Initial check for service cards
        animateServiceCards();
        
        // Combined scroll event listener
        window.addEventListener('scroll', function() {
            animateServiceCards();
        });
    });
    </script>

<script>
    // Add animation for service cards
    document.addEventListener('DOMContentLoaded', function() {
        // Animate service cards
        const serviceCards = document.querySelectorAll('.student_affairs_service_card');
        
        function animateServiceCards() {
            serviceCards.forEach((card, index) => {
                const cardPosition = card.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;
                
                if(cardPosition < screenPosition) {
                    setTimeout(() => {
                        card.classList.add('animated');
                    }, index * 150); // Stagger animation
                }
            });
        }
        
        // Animate timeline events
        const timelineEvents = document.querySelectorAll('.student_affairs_event');
        
        function animateTimelineEvents() {
            timelineEvents.forEach((event, index) => {
                const eventPosition = event.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;
                
                if(eventPosition < screenPosition) {
                    setTimeout(() => {
                        event.style.opacity = '1';
                        event.style.transform = 'translateY(0)';
                    }, index * 200); // Stagger animation
                }
            });
        }
        
        // Initial check for service cards and timeline events
        animateServiceCards();
        animateTimelineEvents();
        
        // Combined scroll event listener
        window.addEventListener('scroll', function() {
            animateServiceCards();
            animateTimelineEvents();
        });
    });

<script>
    // Background image slideshow
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('.student_affairs_about_bg_image');
        let currentIndex = 0;
        
        function nextImage() {
            // Remove active class from current image
            images[currentIndex].classList.remove('active');
            
            // Move to next image
            currentIndex = (currentIndex + 1) % images.length;
            
            // Add active class to new current image
            images[currentIndex].classList.add('active');
        }
        
        // Change image every 5 seconds
        setInterval(nextImage, 5000);
        
        // Add animation for activity cards
        const activityCards = document.querySelectorAll('.student_affairs_activities_card');
        
        function animateActivityCards() {
            activityCards.forEach((card, index) => {
                const cardPosition = card.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;
                
                if(cardPosition < screenPosition) {
                    setTimeout(() => {
                        card.style.opacity = "1";
                        card.style.transform = "translateY(0)";
                    }, index * 150); // Stagger animation
                }
            });
        }
        
        // Set initial opacity and transform for cards
        activityCards.forEach(card => {
            card.style.opacity = "0";
            card.style.transform = "translateY(30px)";
            card.style.transition = "opacity 0.5s ease, transform 0.5s ease";
        });
        
        // Run animation check on load and scroll
        animateActivityCards();
        window.addEventListener('scroll', animateActivityCards);
    });
</script>

    <!-- Add Font Awesome for social icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
    // Animate masonry items on scroll
    document.addEventListener('DOMContentLoaded', function() {
        const masonryItems = document.querySelectorAll('.student_affairs_masonry_item');
        
        // Add initial invisible class
        masonryItems.forEach(item => {
            item.style.opacity = "0";
            item.style.transform = "translateY(20px)";
            item.style.transition = "opacity 0.5s ease, transform 0.5s ease";
        });
        
        function animateMasonryItems() {
            masonryItems.forEach((item, index) => {
                const itemTop = item.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (itemTop < windowHeight * 0.85) {
                    setTimeout(() => {
                        item.style.opacity = "1";
                        item.style.transform = "translateY(0)";
                    }, index * 150); // Stagger the animation
                }
            });
        }
        
        // Run once on load
        setTimeout(animateMasonryItems, 300);
        
        // Run on scroll
        window.addEventListener('scroll', animateMasonryItems);
    });
    </script>

<script>
    // Immediately set up and animate timeline events
    (function() {
        // Function to check if element is in viewport
        function isInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.8
            );
        }
        
        // Animate events function
        function animateEvents() {
            const events = document.querySelectorAll('.student_affairs_event');
            
            events.forEach((event, index) => {
                if (isInViewport(event)) {
                    setTimeout(() => {
                        event.classList.add('visible');
                    }, index * 200);
                }
            });
        }
        
        // Categorize dates function
        function addStatusBadges() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const events = document.querySelectorAll('.student_affairs_event');
            
            events.forEach(event => {
                const dateString = event.getAttribute('data-date');
                if (!dateString) return;
                
                const itemDate = new Date(dateString);
                itemDate.setHours(0, 0, 0, 0);
                
                let status = '';
                let statusClass = '';
                
                if (itemDate.getTime() === today.getTime()) {
                    status = 'Today';
                    statusClass = 'today';
                } else if (itemDate < today) {
                    status = 'Recent';
                    statusClass = 'recent';
                } else {
                    status = 'Upcoming';
                    statusClass = 'upcoming';
                }
                
                // Only add badge if it doesn't exist
                if (!event.querySelector('.event_status_badge')) {
                    const badge = document.createElement('div');
                    badge.className = `event_status_badge ${statusClass}`;
                    badge.textContent = status;
                    
                    const detailsElement = event.querySelector('.student_affairs_event_details');
                    if (detailsElement) {
                        detailsElement.insertBefore(badge, detailsElement.firstChild);
                    }
                }
            });
        }
        
        // Add status badges on load
        document.addEventListener('DOMContentLoaded', function() {
            addStatusBadges();
            animateEvents();
            
            // Set up scroll listener
            window.addEventListener('scroll', animateEvents);
        });
        
        // Run immediately in case DOM is already loaded
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(function() {
                addStatusBadges();
                animateEvents();
            }, 1);
        }
    })();
</script>
