<?php
session_start();

include("connect.php");   // should define $conn (MySQLi)
include("loggedin.php");
include("functions.php");

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

echo '<title>Projects Main</title></head><body>';
include("tables.php");
$conn = getConnection();

if ($flash): echo '<div class="message message-' . $flash['type'] . '">' . htmlspecialchars($flash['text']) . '</div>';
endif;

// Default sorting
$sort = "sort";
$how  = "ASC";
$went = 1;

/**
 * =========================
 * UPDATE SORT ORDER
 * =========================
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$ids   = explode(",", $_POST['ids'] ?? "");
	$order = $_POST;

	unset($order['ids']); // remove ids field

	$order = array_values($order);

	foreach ($order as $index => $val) {

		$id = $ids[$index] ?? null;

		if (!is_numeric($id) || !is_numeric($val)) {
			continue;
		}

		$stmt = $conn->prepare("UPDATE projects SET sort = ? WHERE id = ?");
		$stmt->bind_param("ii", $val, $id);
		$stmt->execute();
	}

	echo '<div class="message message-success">Sort order updated.</div>';
}

/**
 * =========================
 * SORTING LOGIC
 * =========================
 */
if (isset($_GET['sort'])) {

	$good = ["sort", "name", "date", "cat", "status", "last_changed"];
	$sort = $_GET['sort'];

	if (!in_array($sort, $good)) {
		die("Invalid sort column.");
	}

	$how = ($_GET['went'] ?? 1) == 2 ? "DESC" : "ASC";
	$went = ($how === "ASC") ? 2 : 1;
}

echo '<div class="action-bar">';
echo '<a href="editprojects.php?action=create" class="btn btn-primary">Create a new project</a>';
echo '</div>';

echo '<div class="card">';
echo '<div class="card-header">Projects</div>';
echo '<div class="card-body" style="padding:0;">';

/**
 * =========================
 * FETCH PROJECTS
 * =========================
 */
$stmt = $conn->prepare("SELECT * FROM projects ORDER BY $sort $how");
$stmt->execute();
$result = $stmt->get_result();

$total = $result->num_rows;
$dir = ($how === 'ASC') ? ' ^' : ' v';

echo tableHead() . '
<thead>
<tr>
    <th><a href="projects.php?sort=sort&went=' . $went . '">Order' . ($sort === 'sort' ? $dir : '') . '</a></th>
    <th><a href="projects.php?sort=name&went=' . $went . '">Name' . ($sort === 'name' ? $dir : '') . '</a></th>
    <th><a href="projects.php?sort=date&went=' . $went . '">Date' . ($sort === 'date' ? $dir : '') . '</a></th>
    <th><a href="projects.php?sort=cat&went=' . $went . '">Category' . ($sort === 'cat' ? $dir : '') . '</a></th>
    <th><a href="projects.php?sort=status&went=' . $went . '">Status' . ($sort === 'status' ? $dir : '') . '</a></th>
    <th><a href="projects.php?sort=last_changed&went=' . $went . '">Changed' . ($sort === 'last_changed' ? $dir : '') . '</a></th>
</tr>
</thead>
<tbody>

<form method="POST">';

$ids = [];
$x = 0;

while ($row = $result->fetch_assoc()) {

	$ids[] = $row['id'];

	echo '<tr>
        <td>
            <input type="text" name="sort' . $x . '" value="' . $row['sort'] . '" class="sort-input">
        </td>

        <td>
            <a href="editprojects.php?id=' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</a>
        </td>

        <td class="text-muted">' . $row['date'] . '</td>
        <td>' . htmlspecialchars($row['cat']) . '</td>
        <td>' . htmlspecialchars($row['status']) . '</td>
        <td class="text-muted">' . $row['last_changed'] . '</td>
    </tr>';

	$x++;
}

echo '
</tbody>
<tfoot>
<tr>
    <td colspan="6" style="padding:1rem 1rem; background:#f8fafc; border-top:2px solid #e2e8f0;">
        <input type="submit" value="Update Order" class="btn btn-secondary btn-sm">
        <input type="hidden" name="ids" value="' . implode(",", $ids) . '">
    </td>
</tr>
</tfoot>
</table>
</form>';

echo '</div></div>';

/**
 * =========================
 * FOOTER INFO
 * =========================
 */
echo '<div class="footer">';
echo '<span>' . date("n/j/Y h:i:s A") . ' &middot; projects: ' . $total . '</span>';
echo '<span>user: ' . ($_SESSION['user'] ?? 'guest') . '</span>';
echo '<a href="http://phpproject.us" class="text-link">php project</a>';
echo '</div>';
?>
