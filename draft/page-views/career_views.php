<!-- About Section -->
<section class="career_about_section">
    <div class="career_about_container">
        <div class="career_about_header">
            <h2 class="career_about_title">CAREER AND PLACEMENT OFFICE</h2>
            <div class="career_about_divider"></div>
        
        <div class="career_about_content">
            <div class="career_about_image">
                <img src="../imgs/cte.jpg" alt="WMSU Career and Placement Office">
            </div>
            
            <div class="career_about_text">
                <p class="career_about_description">
                    The Western Mindanao State University Career and Placement Office is dedicated to supporting students 
                    and alumni in their career development journey. We provide comprehensive services including career counseling, 
                    resume and interview preparation, job placement assistance, and professional development workshops.
                </p>
                <p class="career_about_description">
                    We actively build partnerships with employers and industries to create employment opportunities 
                    for WMSU graduates. Our office serves as a bridge between academic learning and professional careers, 
                    helping students make successful transitions into the workforce.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="career_mv_wrapper">
    <h2 class="career_mv_main_title">MISSION AND VISION</h2>
    <div class="career_about_divider"></div>
<section class="career_mv_container">
    <div class="career_mv_box_mission" onclick="expandSection(this, 'mission')">
        <img src="../imgs/cte.jpg" alt="WMSU Mission">
        <div class="career_mv_overlay_mission"></div>
        <div class="career_mv_content">
            <h2 class="career_mv_title">MISSION</h2>
            <div class="career_mv_show_more">
                <span class="show_more_icon">+</span>
                <span class="show_more_text">SHOW MORE</span>
            </div>
            <div class="career_mv_full_content">
                <p>The Career and Placement Office is committed to equipping students and graduates with essential career development skills, connecting them with employment opportunities, and fostering partnerships with industries to facilitate successful transitions from academic to professional life.</p>
            </div>
        </div>
    </div>
    
    <div class="career_mv_box_vision" onclick="expandSection(this, 'vision')">
        <img src="../imgs/cte-field.png" alt="WMSU Vision">
        <div class="career_mv_overlay_vision"></div>
        <div class="career_mv_content">
            <h2 class="career_mv_title">VISION</h2>
            <div class="career_mv_show_more">
                <span class="show_more_icon">+</span>
                <span class="show_more_text">SHOW MORE</span>
            </div>
            <div class="career_mv_full_content">
                <p>To be a recognized leader in career development services, renowned for preparing work-ready graduates, establishing strong industry networks, and achieving outstanding employment outcomes for WMSU students across diverse professional fields.</p>
            </div>
        </div>
    </div>
</section>
</section>

<!-- Services Offered Section -->
<section class="career_services_section">
    <div class="career_services_container">
        <div class="career_services_header">
            <h2 class="career_services_title">Services Offered</h2>
        </div>
        <div class="career_services_grid">
            <div class="career_service_card">
                <h3 class="career_service_title">Career Counseling</h3>
                <p class="career_service_description">One-on-one guidance sessions to help you explore career options and make informed decisions about your future.</p>
            </div>
            <div class="career_service_card">
                <h3 class="career_service_title">Resume Building</h3>
                <p class="career_service_description">Professional assistance in creating and optimizing your resume to stand out to potential employers.</p>
            </div>
            <div class="career_service_card">
                <h3 class="career_service_title">Job Placement</h3>
                <p class="career_service_description">Access to job opportunities and assistance in connecting with potential employers.</p>
            </div>
        </div>
    </div>
</section>

<!-- How to Avail Section -->
<section class="career_avail_section">
    <div class="career_avail_container">
        <div class="career_avail_header">
            <h2 class="career_avail_title">How to Avail Our Services</h2>
        </div>
        <div class="career_avail_steps">
            <div class="career_step_card">
                <div class="career_step_number">1</div>
                <h3 class="career_step_title">Schedule an Appointment</h3>
                <p class="career_step_description">Visit our office or use our online scheduling system to book a consultation.</p>
            </div>
            <div class="career_step_card">
                <div class="career_step_number">2</div>
                <h3 class="career_step_title">Initial Consultation</h3>
                <p class="career_step_description">Meet with our career counselor to discuss your needs and goals.</p>
            </div>
            <div class="career_step_card">
                <div class="career_step_number">3</div>
                <h3 class="career_step_title">Follow-up Services</h3>
                <p class="career_step_description">Receive ongoing support and access to resources based on your needs.</p>
            </div>
        </div>
    </div>
</section>

<script>
   function expandSection(element, section) {
    const container = document.querySelector('.career_mv_container');
    const boxes = document.querySelectorAll('.career_mv_box_mission, .career_mv_box_vision');
    
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





<section class="student_affairs_activities_section">
    <div class="student_affairs_activities_container">
        <h2 class="student_affairs_activities_title">UPCOMING EVENTS</h2>
        <div class="student_affairs_activities_divider"></div>
        
        <div class="student_affairs_activities_timeline">
            <!-- Event 1 -->
            <div class="student_affairs_event">
                <div class="student_affairs_event_date">
                    <span class="student_affairs_event_month">MAR</span>
                    <span class="student_affairs_event_day">20</span>
                </div>
                <div class="student_affairs_event_content">
                    <div class="student_affairs_event_image">
                        <img src="../imgs/activities/activity1.jpg" alt="Sports Festival">
                    </div>
                    <div class="student_affairs_event_details">
                        <h3>Sports Festival</h3>
                        <div class="student_affairs_event_meta">
                            <span><i class="fas fa-map-marker-alt"></i> University Gymnasium</span>
                            <span><i class="fas fa-clock"></i> 9:00 AM</span>
                        </div>
                        <p>Join us for a day of fun, games, and healthy competition.</p>
                        <a href="#" class="student_affairs_event_button">Learn More</a>
                    </div>
                </div>
            </div>

            <!-- Event 2 -->
            <div class="student_affairs_event">
                <div class="student_affairs_event_date">
                    <span class="student_affairs_event_month">APR</span>
                    <span class="student_affairs_event_day">15</span>
                </div>
                <div class="student_affairs_event_content">
                    <div class="student_affairs_event_image">
                        <img src="../imgs/activities/activity2.jpg" alt="Career Fair">
                    </div>
                    <div class="student_affairs_event_details">
                        <h3>Career Fair</h3>
                        <div class="student_affairs_event_meta">
                            <span><i class="fas fa-map-marker-alt"></i> Convention Center</span>
                            <span><i class="fas fa-clock"></i> 10:00 AM</span>
                        </div>
                        <p>Explore job opportunities and meet industry experts.</p>
                        <a href="#" class="student_affairs_event_button">Learn More</a>
                    </div>
                </div>
            </div>

            <!-- Event 3 -->
            <div class="student_affairs_event">
                <div class="student_affairs_event_date">
                    <span class="student_affairs_event_month">MAY</span>
                    <span class="student_affairs_event_day">05</span>
                </div>
                <div class="student_affairs_event_content">
                    <div class="student_affairs_event_image">
                        <img src="../imgs/cte.jpg" alt="Leadership Workshop">
                    </div>
                    <div class="student_affairs_event_details">
                        <h3>Leadership Workshop</h3>
                        <div class="student_affairs_event_meta">
                            <span><i class="fas fa-map-marker-alt"></i> Student Center</span>
                            <span><i class="fas fa-clock"></i> 1:00 PM</span>
                        </div>
                        <p>Enhance your leadership skills through hands-on sessions.</p>
                        <a href="#" class="student_affairs_event_button">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

/* Event Item */
.student_affairs_event {
    display: flex;
    position: relative;
    margin-bottom: 4rem;
    opacity: 0;
    transform: translateY(30px);
    animation: fadeInUp 0.6s ease forwards;
    animation-delay: calc(var(--i, 0) * 0.2s);
}

.student_affairs_event:nth-child(odd) {
    flex-direction: row-reverse;
}

.student_affairs_event:nth-child(1) {
    --i: 1;
}

.student_affairs_event:nth-child(2) {
    --i: 2;
}

.student_affairs_event:nth-child(3) {
    --i: 3;
}

/* Date Circle */
.student_affairs_event_date {
    min-width: 100px;
    height: 100px;
    background: #7c0a02;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    position: relative;
    z-index: 2;
    margin: 0 2rem;
    box-shadow: 0 5px 15px rgba(124, 10, 2, 0.3);
}

.student_affairs_event_month {
    font-size: 1rem;
    text-transform: uppercase;
}

.student_affairs_event_day {
    font-size: 2rem;
    line-height: 1;
}

/* Event Content */
.student_affairs_event_content {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    display: flex;
    width: calc(50% - 4rem);
}

.student_affairs_event_image {
    width: 35%;
    overflow: hidden;
    position: relative;
}

.student_affairs_event_image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.student_affairs_event:hover .student_affairs_event_image img {
    transform: scale(1.05);
}

.student_affairs_event_details {
    padding: 1.5rem;
    width: 65%;
}

.student_affairs_event_details h3 {
    font-size: 1.5rem;
    color: #333;
    margin-bottom: 0.75rem;
    font-weight: 600;
}

.student_affairs_event_meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.student_affairs_event_meta i {
    color: #7c0a02;
    margin-right: 0.3rem;
}

.student_affairs_event_details p {
    font-size: 1rem;
    line-height: 1.6;
    color: #444;
    margin-bottom: 1.5rem;
}

.student_affairs_event_button {
    display: inline-block;
    background: #7c0a02;
    color: white;
    padding: 0.6rem 1.5rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.student_affairs_event_button:hover {
    background: #5a0802;
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 992px) {
    .student_affairs_activities_timeline::before {
        left: 30px;
    }
    
    .student_affairs_event,
    .student_affairs_event:nth-child(odd) {
        flex-direction: row;
    }
    
    .student_affairs_event_date {
        min-width: 60px;
        height: 60px;
        margin-left: 0;
        margin-right: 1.5rem;
    }
    
    .student_affairs_event_month {
        font-size: 0.8rem;
    }
    
    .student_affairs_event_day {
        font-size: 1.5rem;
    }
    
    .student_affairs_event_content {
        width: calc(100% - 90px);
    }
}

@media (max-width: 768px) {
    .student_affairs_activities_section {
        padding: 3rem 1.5rem;
    }
    
    .student_affairs_activities_title {
        font-size: 2.5rem;
    }
    
    .student_affairs_event_content {
        flex-direction: column;
    }
    
    .student_affairs_event_image {
        width: 100%;
        height: 200px;
    }
    
    .student_affairs_event_details {
        width: 100%;
    }
    
    .student_affairs_event_meta {
        flex-direction: column;
        gap: 0.5rem;
    }
}

@media (max-width: 576px) {
    .student_affairs_activities_title {
        font-size: 2rem;
    }
    
    .student_affairs_event_details h3 {
        font-size: 1.3rem;
    }
    
    .student_affairs_event_details p {
        font-size: 0.9rem;
    }
}



</script>

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
</script>