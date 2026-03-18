# Volunteer Perks

The Volunteer Perks system lets staff create tiered reward programs tied to volunteer hours. Volunteers can track their progress toward each perk in real time, and staff can manage perks, monitor who has earned them, and handle physical reward redemptions.

> **Feature flag:** The perk tracking system is controlled by the `perk_tracking` application setting. It must be enabled before any perk pages are accessible.

---

## Concepts

### Perk Sets

A **Perk Set** is a container that groups related perks together — typically for a single convention year or event season. Each set can be scoped to a fiscal year, given a visibility window, and toggled active/inactive independently of the perks inside it.

### Perks

A **Perk** is a single reward threshold within a set. It defines the minimum volunteer hours required to earn it, what the volunteer receives, and how progress is measured.

### Hour Sources

Each perk can measure hours in one of two ways:

| Mode | How it works |
|---|---|
| **Event-linked** | One or more specific events are attached to the perk. Only hours from signed-up, non-no-show shifts belonging to those events count. |
| **General hours** | No events are linked. All approved `VolunteerHours` records count, optionally filtered to the perk set's fiscal year. |

---

## Admin: Managing Perk Sets

Navigate to **Admin → Perk Sets**.

### Creating a Perk Set

1. Click **Create Perk Set**.
2. Fill in the fields:

   | Field | Description |
   |---|---|
   | **Set Name** | Display name shown to volunteers (e.g., *MNFurs 2026 Perks*). |
   | **Description** | Optional short description shown on the volunteer perks page. |
   | **Fiscal Year** | Links the set to a fiscal ledger. General-hours perks in this set will only count hours from that fiscal year. |
   | **Visible From** | Date the set starts appearing to volunteers. Leave blank to make it visible immediately once active. |
   | **Visible Until** | Date the set stops showing on the main perks page and moves to **Perk History**. Leave blank to keep it visible indefinitely. |
   | **Sort Order** | Controls the display order relative to other sets. Lower numbers appear first. |
   | **Active** | Uncheck to hide the set from volunteers entirely, regardless of the visibility window. |

3. Click **Create Perk Set**.

### Status Badges

Each set in the list shows one of the following statuses:

| Badge | Meaning |
|---|---|
| **Active** | Visible to volunteers right now. |
| **Upcoming** | Active but `Visible From` is in the future. |
| **Archived** | `Visible Until` date has passed; shown on the volunteer history page. |
| **Inactive** | Manually deactivated; hidden from volunteers. |

### Deleting a Perk Set

Deleting a set does **not** delete the perks inside it. Those perks become *unassigned* and will appear in the "Unassigned Perks" group on the Perks admin page.

---

## Admin: Managing Perks

Navigate to **Admin → Perks**.

Perks are listed grouped by their perk set. Unassigned perks appear at the bottom.

### Creating a Perk

1. Click **Create Perk**.
2. Fill in the fields:

   | Field | Description |
   |---|---|
   | **Perk Name** | The reward name shown to volunteers (e.g., *Free T-Shirt*). Hidden until earned if the perk is a Mystery Perk. |
   | **Description** | Optional details about the reward. Also hidden until earned for Mystery Perks. |
   | **Minimum Hours** | The hour threshold a volunteer must reach to earn this perk. |
   | **Perk Set** | Assigns this perk to a set. Determines the fiscal year scope for general-hours tracking. |
   | **Track Event(s)** | Select one or more events. If selected, only shift hours from those events count. Leave empty to use all volunteer hours. |
   | **Sort Order** | Display order within the perk set. Lower numbers appear first. |
   | **Active** | Uncheck to hide this perk from volunteers. |
   | **Mystery Perk** | See [Mystery Perks](#mystery-perks) below. |
   | **Access Pass** | See [Access Passes](#access-passes) below. |
   | **Physical Reward** | See [Physical Rewards](#physical-rewards) below. |

3. Click **Create Perk**.

---

## Mystery Perks

When **Hide this perk until earned** is checked on a perk, it becomes a *Mystery Perk*.

**Volunteer experience (unearned):** The perk card on the volunteer perks page shows only a question-mark placeholder with the message *"Keep volunteering to unlock and reveal this perk!"* The name, description, progress bar, and any reward actions are fully hidden.

**Volunteer experience (earned):** Once the volunteer's hours meet the threshold, the full perk card is revealed — name, description, progress, and any pass or redemption buttons appear as normal.

**Admin experience:** The perk's name and all details are always visible to admins. A purple **Mystery** badge appears in the perk list and on the Perk Awards report.

---

## Access Passes

An **Access Pass** is a digital pass a volunteer can show on their phone to gain entry to a restricted area (e.g., a VIP lounge).

To enable:

1. Check **This perk includes an access pass** on the perk form.
2. Enter a **Pass Label** describing the access granted (e.g., *VIP Lounge Access*).

**Volunteer experience:** Once earned, a **Show Pass** button appears on the perk card. Tapping it opens a full-screen modal displaying the volunteer's name, the perk name, and an animated color-cycling banner with a live clock to confirm the time of presentation.

---

## Physical Rewards

A **Physical Reward** is a one-time redeemable item (e.g., a t-shirt or convention coupon) collected in person at a designated location.

To enable:

1. Check **This perk includes a redeemable physical reward** on the perk form.
2. Enter a **Reward Label** describing what the volunteer receives (e.g., *Convention T-Shirt*).

**Volunteer experience:** Once earned, a **Redeem** button appears with a warning to only press it when ready to collect or when asked by a staff member. After pressing:

- A **10-minute countdown window** displays so staff can verify the redemption in real-time.
- After the window closes, the card shows **Already Redeemed** and the button is gone.
- Redemption is permanent and cannot be undone by the volunteer.

**Admin management:** Go to **Admin → Perks**, then click **Redemptions** next to the perk. This lists every volunteer who has redeemed it with their name and the timestamp. Admins can **reset** a redemption (e.g., if it was pressed accidentally), which allows the volunteer to redeem again.

---

## Perk Awards Report

The **Perk Awards** report shows which volunteers have earned each perk within a given perk set.

### Accessing the report

1. Go to **Admin → Perk Sets**.
2. Click **Perk Awards** in the Actions column for the desired set.

### Reading the report

The page opens with a summary bar showing:
- Total number of perks in the set.
- Total number of **unique earners** across all perks.

Below that, each perk has its own card showing:

| Column | Description |
|---|---|
| **Name** | The volunteer's display name. |
| **Vol Code** | Their unique volunteer code. |
| **Email** | Their account email address. |
| **Redemption** *(physical reward perks only)* | Shows the redemption timestamp if redeemed, or **Not redeemed** if they have earned the perk but not yet collected it. |

Perks with no earners yet display *"No volunteers have earned this perk yet."*

> **Note:** Hour counts used to determine earners in the report match the same logic volunteers see on their progress page — event-linked perks use shift data; general-hours perks use approved `VolunteerHours` records.

---

## Volunteer Experience

Volunteers access perks at **Volunteer Perks** in the navigation.

### Current Perks page

Shows all active perk sets currently within their visibility window. For each perk:

- A **split progress bar** displays completed hours (green) and upcoming hours from shifts not yet finished (yellow).
- An **On Track** badge appears when the volunteer's total (completed + upcoming) meets the threshold but completed hours alone do not yet meet it.
- An **Earned** badge and green highlight appear once completed hours meet or exceed the threshold.
- **Mystery Perks** that have not yet been earned show only a purple placeholder card.

### Perk History page

Accessible from the **View History** link. Shows all archived perk sets (those whose `Visible Until` date has passed) with read-only progress bars so volunteers can see their final record.

### Progress calculation

Hours are calculated identically for the volunteer-facing page and the admin Perk Awards report:

1. **Event-linked perks:** Sums the duration of all shifts the volunteer is signed up for within the linked events, excluding no-shows. Shifts that use **Double Hours** count at 2×.
2. **General-hours perks:** Sums all approved `VolunteerHours` records for the volunteer, filtered to the perk set's fiscal year if one is set.

> Hours shown include *upcoming* shifts (not yet completed) on the volunteer progress page, so the displayed total may decrease if a shift is cancelled or the volunteer is marked a no-show. The warning banner on the perks page reflects this.
