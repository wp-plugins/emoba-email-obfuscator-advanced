function emobascript(email, ename, id, hover) {
	var cleanName = ename.replace(/&lt;/g, '<');
  var mailtoString = 'mailto:';
  var mailNode = document.getElementById(id);
  var linkNode = document.createElement('a');
  linkNode.title = 'Send email';
  linkNode.id = id;
  var mailtolink = mailtoString + email;
  linkNode.href = mailtolink;
	if (hover) {
		var spanNode = document.createElement('span');
		spanNode.className = 'emoba-hover';
		spanNode.innerHTML = 'Click to email ';
		linkNode.appendChild(spanNode);
		linkNode.className = 'emoba-pop';
	}
  var tNode = document.createElement('span');
  tNode.innerHTML = cleanName;
  linkNode.appendChild(tNode);
  mailNode.parentNode.replaceChild(linkNode, mailNode);
}