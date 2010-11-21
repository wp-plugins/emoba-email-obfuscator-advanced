/** Version 1.6.5 **/
function emobascript(email, ename, id, eclass, estyle, hover) {
	var cleanName = ename.replace(/&lt;/g, '<');
  var mailtoString = 'mailto:';
  var mailNode = document.getElementById(id);
  var linkNode = document.createElement('a');
  linkNode.className = eclass+'emoba-link';
  linkNode.title = 'Send email';
  linkNode.id = id;
  if (estyle) {linkNode.setAttribute('style', estyle);}
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
  tNode.className = 'emoba-realname';
  tNode.innerHTML = cleanName;
  linkNode.appendChild(tNode);
  mailNode.parentNode.replaceChild(linkNode, mailNode);
}