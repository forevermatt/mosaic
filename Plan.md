A. Get guide image.
B. Get source images.
C. Slice up guide image.
D. Match up source images with slices of guide image.
E. Assemble and save mosaic.

---

C.1. Count source images.
  2. Slice guide image into no more than that many slices.

---

D.1. Get signature of each source image.
  2. For each slice, find the best match in the source images, not excluding any yet.

---

D.1.a. Downsize the slice to 1 pixel. Record color.
    b. If/when necessary, up to a finite limit, downsize the original slice to
       four (4) times as many pixels as last time. Record colors.

---

D.2.a. If the 1-pixel color color matches at least as well as any other source
       images so far, keep it. Forget any that don't match as well.
    b. If we have several equal matches, compare the next higher resolution.
       Repeat until we found a "best" match (up to a finite limit).
    c. Record how accurate a match it was.

---

