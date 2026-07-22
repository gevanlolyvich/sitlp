<header class="app-header navbar navbar-expand bg-body">

<div class="container-fluid">

<ul class="navbar-nav">

<li class="nav-item">

<a
class="nav-link"
data-lte-toggle="sidebar"
href="#">

<i class="fas fa-bars"></i>

</a>

</li>

</ul>

<ul class="navbar-nav ms-auto">

<li class="nav-item">

<span class="nav-link">

<?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?>

</span>

</li>

</ul>

</div>

</header>
