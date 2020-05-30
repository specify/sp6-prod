# sp6-prod

Specify 6 stats recording and related services.

Legacy code. Origin uncertain.

The `public` directory is meant to be served at `specify6-prod.nhm.ku.edu`. 
The `private` directory is meant to be served on an internal network for viewing reports.

MySql credentials are read from `/etc/myauth`. Two append-only data files are accessed. I have hardcoded these to
`/home/anhalt/reg.dat` and `/home/anhalt/track.dat`.
