<!DOCTYPE html>
<html lang="en">
    <head>
        @include('home.partials.head')
    </head>
    <body id="page-top">

        @include('home.partials.menu')
        @include('home.partials.banner')
        
       
        <!-- About-->
        <section class="about-section text-center" id="about">
            <div class="container px-4 px-lg-5">
                <div class="row gx-4 gx-lg-5 justify-content-center">
                    <div class="col-lg-8">
                        <h2 class="text-white mb-4">Built with Bootstrap 5</h2>
                        <p class="text-white-50">
                            Grayscale is a free Bootstrap theme created by Start Bootstrap. It can be yours right now, simply download the template on
                            <a href="https://startbootstrap.com/theme/grayscale/">the preview page.</a>
                            The theme is open source, and you can use it for any purpose, personal or commercial.
                        </p>
                    </div>
                </div>
                <img class="img-fluid" src="{{ asset('home/images/ipad.png') }}" alt="..." />
            </div>
        </section>
        <!-- Projects-->
        <section class="projects-section bg-light" id="projects">
            <div class="container px-4 px-lg-5">
                <!-- Featured Project Row-->
                <div class="row gx-0 mb-4 mb-lg-5 align-items-center">
                    <div class="col-xl-8 col-lg-7"><img class="img-fluid mb-3 mb-lg-0" src="{{ asset('home/images/bg-masthead.jpg') }}" alt="..." /></div>
                    <div class="col-xl-4 col-lg-5">
                        <div class="featured-text text-center text-lg-left">
                            <h4>Shoreline</h4>
                            <p class="text-black-50 mb-0">Grayscale is open source and MIT licensed. This means you can use it for any project - even commercial projects! Download it, customize it, and publish your website!</p>
                        </div>
                    </div>
                </div>
                <!-- Project One Row-->
                <div class="row gx-0 mb-5 mb-lg-0 justify-content-center">
                    <div class="col-lg-6"><img class="img-fluid" src="{{ asset('home/images/demo-image-01.jpg') }}" alt="..." /></div>
                    <div class="col-lg-6">
                        <div class="bg-black text-center h-100 project">
                            <div class="d-flex h-100">
                                <div class="project-text w-100 my-auto text-center text-lg-left">
                                    <h4 class="text-white">Misty</h4>
                                    <p class="mb-0 text-white-50">An example of where you can put an image of a project, or anything else, along with a description.</p>
                                    <hr class="d-none d-lg-block mb-0 ms-0" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Project Two Row-->
                <div class="row gx-0 justify-content-center">
                    <div class="col-lg-6"><img class="img-fluid" src="{{ asset('home/images/demo-image-02.jpg') }}" alt="..." /></div>
                    <div class="col-lg-6 order-lg-first">
                        <div class="bg-black text-center h-100 project">
                            <div class="d-flex h-100">
                                <div class="project-text w-100 my-auto text-center text-lg-right">
                                    <h4 class="text-white">Mountains</h4>
                                    <p class="mb-0 text-white-50">Another example of a project with its respective description. These sections work well responsively as well, try this theme on a small screen!</p>
                                    <hr class="d-none d-lg-block mb-0 me-0" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Signup-->
        <section class="signup-section" id="signup">
            <div class="container px-4 px-lg-5">
                <div class="row gx-4 gx-lg-5">
                    <div class="col-md-10 col-lg-8 mx-auto text-center">
                        <i class="far fa-paper-plane fa-2x mb-2 text-white"></i>
                        <h2 class="text-white mb-5">Subscribe to receive updates!</h2>
                        <form class="form-signup d-flex flex-column flex-sm-row">
                            <input class="form-control flex-fill me-0 me-sm-2 mb-3 mb-sm-0" id="inputEmail" type="email" placeholder="Enter email address..." />
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <!-- Contact-->
        <section class="contact-section bg-black">
            <div class="container px-4 px-lg-5">
                <div class="row gx-4 gx-lg-5">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-map-marked-alt text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Address</h4>
                                <hr class="my-4 mx-auto" />
                                <div class="small text-black-50">4923 Market Street, Orlando FL</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Email</h4>
                                <hr class="my-4 mx-auto" />
                                <div class="small text-black-50"><a href="#!">hello@yourdomain.com</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-mobile-alt text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Phone</h4>
                                <hr class="my-4 mx-auto" />
                                <div class="small text-black-50">+1 (555) 902-8832</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="social d-flex justify-content-center">
                    <a class="mx-2" href="#!"><i class="fab fa-twitter"></i></a>
                    <a class="mx-2" href="#!"><i class="fab fa-facebook-f"></i></a>
                    <a class="mx-2" href="#!"><i class="fab fa-github"></i></a>
                </div>
            </div>
        </section>
        <!-- Footer-->
        <footer class="footer bg-black small text-center text-white-50"><div class="container px-4 px-lg-5">Copyright &copy; Your Website 2021</div></footer>
        <!-- Bootstrap core JS-->
    </body>
</html>
