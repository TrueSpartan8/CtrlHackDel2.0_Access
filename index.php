<html>
    <head>
        <title>
            Access
        </title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div>
            <img src="images/LogoBlack.png" alt="logo" id="mainLogo">
            <h2>
                <a href="#form" id="findCareLink">FIND CARE</a>
            </h2>
        </div>
        <div id="aboutUs">
            <h1 id="aboutUsTitle">About Us</h1>
            <p id="aboutUsText">
                 In Ontario, patients can spend over a year waiting for the care they need. That's a year of suffering, lowered quality of life, and time for symptoms to progress before treatment even begins. Ancillary care services can help fill in the gaps, but with so many options and so few ways to narrow down your search, finding the right care for your needs can feel impossible.
                 <br>
                 <br>
                 With Access, patients can use our accessible filter system and chatbot to sort through hundreds of providers and practitioners to find the best fit for their needs, budget, and geographic location. We use the information you provide, mapping, and our service registry to make your life easier. 
                 <br>
                 <br>
                 We'll find you options; you just focus on getting healthy. 
            </p>
            <img src="images/plus.png" id="plusImage">
        </div>
        <div>
            <img src="images/Access-ception.png" id="laptopImage">
        </div>
        <div>
            <h1 id="careTitle">
                We make all our recommendations with your C.A.R.E. in mind:
            </h1>

            <h2 id="careSubtitle">
                Ancillary Care should be...
            </h2>
            <table>
                <tr>
                    <td>
                        <img src="images/stopwatch.png" id="careIcon">
                    </td>
                    <td>
                        <h2>Convenient</h2>
                        <p>Medical issues are hard enough. Getting to your appointment shouldn't be. </p>
                    </td>
                    <td>
                        <img src="images/bullseye.png" id="careIcon">
                    </td>
                    <td>
                        <h2>Realistic</h2>
                        <p>We recommend providers that align with your location and needs, no strings attached.</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="images/handshake.png" id="careIcon">
                    </td>
                    <td>
                        <h2>Approachable</h2>
                        <p>Searching for ancillary care should take minutes – not hours of sifting through search results.</p>
                    </td>
                    <td>
                        <img src="images/heart.png" id="careIcon">
                    </td>
                    <td>
                        <h2>Efficient</h2>
                        <p>Investing time and money in treatment should make a difference. Get care that works best for YOU, not a hypothetical patient.</p>
                    </td>
            </table>
        </div>
        <br><br><br><br><br>
        <div id="form" class="form-wrapper">
            <!-- Loading screen -->
            <div id="loadingScreen" style="display: none;">
                <div style="text-align: center;">
                    <div class="spinner"></div>
                    <h2 style="margin-top: 20px; font-family: title;">Finding Your Care Matches...</h2>
                    <p style="font-family: body;">Please wait while we search our network</p>
                </div>
            </div>

            <form action="find-care.php" method="POST" id="careForm" onsubmit="showLoading()">
                <h1>Find Care Now</h1>
                <table>
                    <tr height=fit-content>
                        <td>
                            <h3>Age:</h3>
                            <select name="age">
                                <option value="0-17">Under 18</option>
                                <option value="18-29">18-29</option>
                                <option value="30-49">30-49</option>
                                <option value="50-64">50-64</option>
                                <option value="65+">65+</option>
                            </select>
                        </td>
                        <td>
                            <h3>Sex:</h3>
                            <select name="sex">
                                <option value="select">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </td>
                        <td>
                            <h3>Gender Identity:</h3>
                            <select name="gender_identity">
                                <option value="select">Select</option>
                                <option value="cisgenderMan">Cisgender Man</option>
                                <option value="cisgenderWoman">Cisgender Woman</option>
                                <option value="transgenderMan">Transgender Man</option>
                                <option value="transgenderWoman">Transgender Woman</option>
                                <option value="nonBinary">Non-Binary</option>
                                <option value="other">Other</option>
                            </select>
                        </td>
                    </tr>
                    <tr height=fit-content>
                        <td>
                            <h3>Preferred Practitioner Gender:</h3>
                            <select name="practitioner_gender">
                                <option value="noPreference">No Preference</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </td>
                        <td>
                            <h3>Location (City):</h3>
                            <input type="text" name="location" placeholder="Enter your city" required>
                        </td>
                        <td>
                            <h3>Radius:</h3>
                            <select name="radius">
                                <option value="5">5 km</option>
                                <option value="10">10 km</option>
                                <option value="25">25 km</option>
                                <option value="50">50 km</option>
                                <option value="100">100 km</option>
                            </select>
                        </td>
                    </tr>
                    <tr height=fit-content>
                        <td colspan="3">
                            <h3>Symptoms:</h3>
                            <textarea name="symptoms" placeholder="Enter your symptoms" required></textarea>
                        </td>
                    </tr>
                </table>
                <input type="submit" value="Submit">
            </form>
        </div>

        <script>
            function showLoading() {
                // Hide the form
                document.getElementById('careForm').style.display = 'none';
                // Show loading screen
                document.getElementById('loadingScreen').style.display = 'flex';
                // Allow form to submit
                return true;
            }
        </script>
    </body>
</html>