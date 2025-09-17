<?php
include '../config.php'; session_start();
if (!isset($_SESSION['is_admin'])) header('Location: login.php');
$result = mysqli_query($conn,"SELECT id,name,email,created_at FROM users ORDER BY created_at DESC");
include 'includes/admin-header.php';
?>
<h2>All Users</h2>
<table class="budgets-table">
  <thead>
    <tr>
      <th>Name</th><th>Email</th><th>Joined</th><th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($u = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= htmlspecialchars($u['created_at']) ?></td>
      <td>
        <a href="view-user.php?id=<?=$u['id']?>">View</a> |
        <a href="delete-user.php?id=<?=$u['id']?>" onclick="return confirm('Delete this user?')">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php include 'includes/admin-footer.php'; ?>
<style>
/* Admin Users Table Styling */
.budgets-table {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5em 0;
  font-size: 0.95rem;
}

.budgets-table th,
.budgets-table td {
  padding: 0.75em 1em;
  border: 1px solid #ddd;
  text-align: left;
}

.budgets-table thead {
  background: #005f73;
  color: #fff;
}

.budgets-table tbody tr:nth-child(even) {
  background: #f9f9f9;
}

.budgets-table tbody tr:hover {
  background: #e9f9fd;
}

.budgets-table a {
  color: #005f73;
  text-decoration: none;
}

.budgets-table a:hover {
  text-decoration: underline;
}


   /* Admin Dashboard Styles */
body{
    background:rgb(211, 196, 121);
}
/* Card Container + Cards */
.admin-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin: 2rem 0;
}
.admin-cards .card {
  background: #fff;
  border-radius: 0.5rem;
  padding: 1.5rem;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  flex: 1 1 200px;
  min-width: 200px;
  text-align: center;
}
.admin-cards .card p {
  font-size: 2rem;
  margin: 0.5rem 0;
  color: #005f73;
}

/* Button Style */
.btn.tertiary {
  display: inline-block;
  margin: 0.5rem 0.25rem;
  padding: 0.6rem 1.2rem;
  border: 2px solid #94d2bd;
  border-radius: 0.4rem;
  background: transparent;
  color: #005f73;
  text-decoration: none;
  transition: background 0.3s, color 0.3s;
}
.btn.tertiary:hover {
  background: #94d2bd;
  color: #fff;
}

/* Page Header */
.page-intro {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  margin: 2rem 0;
}
.page-intro h2 {
  font-size: 1.8rem;
}

/* Responsive Behavior */
@media (max-width: 768px) {
  .admin-cards {
    flex-direction: column;
  }
  .page-intro {
    flex-direction: column;
    align-items: flex-start;
  }
}

/* Navbar Adjustments */
.site-header nav a {
  margin: 0 0.75rem;
  color: #fff;
}
.site-header nav a:hover {
  opacity: 0.85;
}


    </style>
    