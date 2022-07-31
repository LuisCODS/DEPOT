	
	<!-- ______________________  NAVBAR VISITEUR   ______________________-->
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-5">        
		<a class="navbar-brand" href="#">Films</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
			aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Alterna navegação">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarNavDropdown">
			<ul class="navbar-nav">
				<li class="nav-item active">
					<a class="nav-link" href="../../view/home/index.php"><i 
					class="fas fa-home"></i><span class="sr-only">(Página atual)</span>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="../../view/film/lister.php">Nos films</a>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink"
						role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						Categories
					</a>
				<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
					<a class="dropdown-item" href="../../view/film/lister.php?cat=action">Action</a>
					<a class="dropdown-item" href="../../view/film/lister.php?cat=comedie">Comedie</a>
					<a class="dropdown-item" href="../../view/film/lister.php?cat=romance">Romance</a>
					<a class="dropdown-item" href="../../view/film/lister.php?cat=drame">Drame</a>
					<a class="dropdown-item" href="../../view/film/lister.php?cat=horreur">Horreur</a>
				</div>
				</li>
				<li class="nav-item">                                 
					<a class="nav-link" href="../../view/login/index.php">Connection  <i class="fas fa-sign-in-alt"></i></a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="../../view/membre/create.php">Devenir membre <i class="far fa-user"></i></a>
				</li>
			</ul>
		</div>
	</nav>
	