<nav class="vertical_nav">
        <div class="left_section menu_left" id="js-menu">
            <div class="left_section">
                <ul>
                    <li class="menu--item">
                        <a href="index.php" class="menu--link  <?php if($path=='Dashboard') echo 'active' ?>" title="Dashboard">
                            <i class="uil uil-apps menu--icon"></i>
                            <span class="menu--label">Dashboard</span>
                        </a>
                    </li>
                    <li class="menu--item">
                        <a href="earning.php?path=Easy English&major=english" class="menu--link  <?php if($path=='Easy English') echo 'active' ?>" title="Courses">
                            <i class='uil uil-book-alt menu--icon'></i>
                            <span class="menu--label">Easy English</span>
                        </a>
                    </li>
                    <li class="menu--item">
                        <a href="earning.php?path=Easy Korean&major=korea" class="menu--link <?php if($path=='Easy Korean') echo 'active' ?>" title="Analyics">
                            <i class='uil uil-book-alt menu--icon'></i>
                            <span class="menu--label">Easy Korean</span>
                        </a>
                    </li>
                    
                </ul>
            </div>

            <div class="left_section">
                <ul>
                    <li class="menu--item">
                        <a href="remaining-balance.php" class="menu--link  <?php if($path=='remaining-balance') echo 'active' ?>" title="Dashboard">
                             <i class='uil uil-wallet menu--icon'></i>
                            <span class="menu--label">Remaining Balance</span>
                        </a>
                    </li>
                     
                </ul>

            </div>
        </div>
    </nav>