<style>
:root {
    --skinColor1: #f05a28;
}

.menubar {
    background-color: transparent;
}

.search-input {
    background-color: transparent;
}

.search-input:focus,
.search-input.focused {
    background-color: #fff;
}

.elegant-banner-image {
    max-width: 500px;
    width: 85%;
    height: auto;
}

@media (min-width: 992px) {
    .elegant-banner-image {
        margin-top: -38px;
    }
}

/* Section Title */
.home1-section-title {
    max-width: 621px;
    width: 100%;
    margin: 0 auto 30px auto;
}

.home1-section-title .title {
    font-family: 'SF Pro Display';
    font-weight: 700;
    font-size: 32px;
    line-height: 36px;
    color: #0d221d;
    text-align: center;
}

.home1-section-title .info {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 16px;
    line-height: 24px;
    text-align: center;
    color: #858c8a;
    text-align: center;
}

/* Course Card */
.course-card1-link {
    display: block;
    width: 100%;
    height: 100%;
    border-radius: 12px;
    padding: 14px;
    box-shadow: 0 14px 32px 0 rgba(147, 148, 158, 0.2);
    background: var(--whiteColor);
}

.course-card1-link .banner {
    width: 100%;
    aspect-ratio: 242 / 190;
    margin-bottom: 14px;
}

.course-card1-link .banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.course-card1-details .rating-reviews {
    gap: 6px;
    margin-bottom: 4px;
}

.course-card1-details .rating-reviews .rating {
    gap: 4px;
}

.course-card1-details .rating-reviews .reviews {
    font-family: 'SF Pro Display';
    font-weight: 400;
    font-size: 14px;
    line-height: 20px;
    color: #858c8a;
}

.course-card1-details .title-info {
    margin-bottom: 12px;
}

.course-card1-details .title-info .title {
    font-family: 'SF Pro Display';
    font-weight: 700;
    font-size: 20px;
    line-height: 28px;
    color: #0d221d;
    margin-bottom: 2px;
    white-space: nowrap;
    width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
}

.course-card1-details .title-info .info {
    font-family: 'SF Pro Display';
    font-weight: 500;
    font-size: 14px;
    line-height: 20px;
    color: #858c8a;
    white-space: nowrap;
    width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
}

.course-card1-leasons-students {
    column-gap: 12px;
    row-gap: 8px;
}

.course-card1-leasons-students .leasons-students {
    column-gap: 4px;
    margin-bottom: 8px;
}

.course-card1-leasons-students .leasons-students .total {
    font-family: 'SF Pro Display';
    font-weight: 400;
    font-size: 14px;
    line-height: 20px;
    color: #858c8a;
}

.course-card1-author-price {
    column-gap: 20px;
}

.course-card1-author-price .author {
    column-gap: 8px;
}

.course-card1-author-price .author .profile {
    width: 30px;
    height: 30px;
}

.course-card1-author-price .author .profile img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.course-card1-author-price .author .name {
    font-family: 'SF Pro Display';
    font-weight: 500;
    font-size: 12px;
    line-height: 16px;
    color: #0d221d;
}

.course-card1-author-price .prices .new-price {
    font-family: 'Inter';
    font-weight: 700;
    font-size: 20px;
    line-height: 28px;
    color: var(--skinColor1);
    text-align: right;
}

.course-card1-author-price .prices .old-price {
    font-family: 'Inter';
    font-weight: 600;
    font-size: 14px;
    line-height: 20px;
    text-decoration: line-through;
    color: #858c8a;
    text-align: right;
}

.course-card1-inner .leasons-students img {
    filter: hue-rotate(95deg);
}


/* Why choose us start */
.why-choose-section1 {
    background: url(assets/frontend/default-new/image/img/choose1-background.svg) no-repeat scroll center center / cover;
    position: relative;
    z-index: 1;
    overflow: hidden;
    filter: hue-rotate(95deg);
}

.why-choose-section1::after {
    position: absolute;
    content: "";
    right: 0;
    top: 39.09px;
    width: 482px;
    aspect-ratio: 482 / 263;
    background: url(assets/frontend/default-new/image/shape/choose-shape-1.svg) no-repeat scroll center center / cover;
    z-index: -1;
    filter: hue-rotate(95deg);
}

.why-choose-section1::before {
    position: absolute;
    content: "";
    left: 0;
    bottom: 39.09px;
    width: 482px;
    aspect-ratio: 482 / 263;
    background: url(assets/frontend/default-new/image/shape/choose-shape-2.svg) no-repeat scroll center center / cover;
    z-index: -1;
    filter: hue-rotate(95deg);
}

.why-choose-area1 {
    padding: 60px 0px;
}

.why-choose-area1>.title {
    font-family: 'Ubuntu';
    font-weight: 700;
    font-size: 32px;
    line-height: 36px;
    color: #0d221d;
    text-align: center;
}

.why-choose-wrap1 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    row-gap: 30px;
}

.why-choose1-single {
    justify-self: center;
    position: relative;
    width: 100%;
}

.why-choose1-single:not(:last-child):after {
    position: absolute;
    content: "";
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    height: 77px;
    width: 1px;
    background: rgba(133, 140, 138, 0.3);
}

.why-choose1-single .total {
    font-family: 'Ubuntu';
    font-weight: 700;
    font-size: 72px;
    line-height: 76px;
    color: #0d221d;
    margin-bottom: 12px;
    text-align: center;
}

.why-choose1-single .info {
    font-family: 'SF Pro Display';
    font-weight: 500;
    font-size: 18px;
    line-height: 28px;
    color: #0d221d;
    text-align: center;
}

/* Why choose us end */

.custom-accordion-two .accordion-item:has(.show)::before {
    background: #dadcdc;
}

.custom-accordion-two .accordion-item .accordion-header .accordion-button {
    padding: 0px;
}

.custom-accordion-two .accordion-item .accordion-body {
    padding: 0px 30px 18px 0px;
}

.home1-section-title .title {
    font-size: 36px;
    font-weight: 500;
    font-family: 'SF Pro Display';
    line-height: 50px;
    padding-bottom: 15px;
}

.lms-hero-section2 {
    background: url(assets/frontend/default-new/image/img/corpo-hero-bg.webp) no-repeat scroll center center / cover;
}

/* davaindia Pharmacist Learning Academy Styles */
.lms1-btn-purple {
    background: linear-gradient(135deg, #f05a28 0%, #d44716 100%) !important;
    border: 1px solid #d44716 !important;
    color: #ffffff !important;
    box-shadow: 0 8px 22px rgba(240, 90, 40, 0.32) !important;
    font-weight: 600 !important;
    letter-spacing: 0.3px;
    border-radius: 12px !important;
    padding: 12px 28px !important;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
.lms1-btn-purple:hover {
    background: linear-gradient(135deg, #d44716 0%, #b83a0f 100%) !important;
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(240, 90, 40, 0.45) !important;
    color: #ffffff !important;
}
.lms1-btn-outline-pharma {
    background: #ffffff;
    border: 2px solid #2e6930;
    color: #2e6930 !important;
    font-family: "SF Pro Display", sans-serif;
    font-size: 16px;
    font-weight: 600;
    padding: 11px 24px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 4px 14px rgba(46, 105, 48, 0.12);
}
.lms1-btn-outline-pharma:hover {
    background: #2e6930;
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(46, 105, 48, 0.28);
}
.pharma-hero-pill-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 18px;
    background: linear-gradient(135deg, rgba(240, 90, 40, 0.09) 0%, rgba(46, 105, 48, 0.09) 100%);
    border: 1px solid rgba(240, 90, 40, 0.25);
    border-radius: 50px;
    color: #f05a28;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.3px;
    backdrop-filter: blur(4px);
    max-width: 100%;
}
@media (max-width: 576px) {
    .pharma-hero-pill-badge {
        font-size: 11px;
        padding: 5px 12px;
        line-height: 1.4;
    }
}
.badge-icon-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #2e6930;
    box-shadow: 0 0 8px #2e6930;
    animation: pulsePharmaGreen 2s infinite;
}
@keyframes pulsePharmaGreen {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(46, 105, 48, 0.7); }
    70% { transform: scale(1.05); box-shadow: 0 0 0 6px rgba(46, 105, 48, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(46, 105, 48, 0); }
}
.avatar-pharma-chip {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    border: 2px solid #ffffff;
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}
.pharma-floating-badge1, .pharma-floating-badge2 {
    backdrop-filter: blur(14px);
    background: rgba(255, 255, 255, 0.95) !important;
    border: 1px solid rgba(255, 255, 255, 0.9);
    border-radius: 16px !important;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08) !important;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.pharma-floating-badge1:hover, .pharma-floating-badge2:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 42px rgba(15, 23, 42, 0.12) !important;
}
.brand-slide1 {
    padding: 10px 22px;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #edf0f4;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
    transition: all 0.3s ease;
}
.brand-slide1:hover {
    box-shadow: 0 8px 24px rgba(240, 90, 40, 0.12);
    border-color: rgba(240, 90, 40, 0.3);
    transform: translateY(-2px);
}
.lms-category-type1 {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    text-decoration: none;
    display: block;
}
.lms-category-type1:hover {
    transform: translateY(-6px);
}
.lms-category-type1 .category-type1-banner {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 26px rgba(0, 0, 0, 0.07);
    border: 2px solid transparent;
    transition: all 0.3s ease;
    background: #f8fafc;
}
.lms-category-type1:hover .category-type1-banner {
    border-color: #f05a28;
    box-shadow: 0 16px 36px rgba(240, 90, 40, 0.22);
}
.category-type1-title {
    font-weight: 700 !important;
    font-size: 15px !important;
    color: #1e293b !important;
    text-align: center;
    margin-top: 10px;
    transition: color 0.3s ease;
}
.lms-category-type1:hover .category-type1-title {
    color: #f05a28 !important;
}
.lms1-course-card {
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #edf0f5;
    transition: all 0.35s ease;
    background: #ffffff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
}
.lms1-course-card:hover {
    border-color: rgba(240, 90, 40, 0.3);
    box-shadow: 0 18px 40px rgba(240, 90, 40, 0.12);
    transform: translateY(-5px);
}
.lms1-cCard-banner {
    border-radius: 14px 14px 0 0;
    overflow: hidden;
}
.lms1-cCard-banner .banner {
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.lms1-course-card:hover .lms1-cCard-banner .banner {
    transform: scale(1.06);
}
.lms1-cCard-title {
    font-size: 17px !important;
    font-weight: 700 !important;
    line-height: 1.45 !important;
    color: #0f172a !important;
    min-height: 48px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.lms1-btn-dark {
    background: linear-gradient(135deg, #2e6930 0%, #1e4d20 100%) !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    border: none !important;
    padding: 6px 14px !important;
}
.lms1-btn-dark:hover {
    background: linear-gradient(135deg, #1e4d20 0%, #153717 100%) !important;
    color: #ffffff !important;
}

/* Header & Topbar Branding */
.ctBtn {
    background: linear-gradient(135deg, #f05a28 0%, #d44716 100%) !important;
    padding: 8px 22px !important;
    border-radius: 8px !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    box-shadow: 0 4px 14px rgba(240, 90, 40, 0.28) !important;
    transition: all 0.3s ease !important;
}
.ctBtn:hover {
    background: linear-gradient(135deg, #d44716 0%, #b83a0f 100%) !important;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(240, 90, 40, 0.4) !important;
    color: #ffffff !important;
}
.menubar .right-menubar .menu_number {
    background: #f05a28 !important;
}

</style>
<link rel="stylesheet" href="<?php echo base_url('assets/frontend/default-new/css/swiper-bundle.min.css'); ?>">
<script src="<?php echo base_url('assets/frontend/default-new/js/swiper-bundle.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/frontend/default-new/js/counterUp.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/frontend/default-new/js/jquery.waypoints.min.js'); ?>"></script>
