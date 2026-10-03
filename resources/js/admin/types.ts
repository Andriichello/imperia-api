import type {EditorDashboard} from '@/api'

/** A restaurant the admin can edit (see `RestaurantEditorRepository::editableBy()`). */
export interface AdminRestaurant {
  id: number
  slug: string | null
  name: string
  default_locale: string
  // its cover
  photo: string | null
}

/** Pages of the admin (see `AdminPageController::signedIn()`). */
export interface AdminUrls {
  dashboard: string
  editor: string
  logout: string
  // the admin panel, for the ones, who can open it
  panel: string | null
  versions: string
  // of a version: this one and `/{id}`
  version: string
}

export interface AdminUser {
  id: number
  name: string
  email: string
}

/** Props of the dashboard (see `DashboardController`). */
export interface DashboardProps {
  locale: string
  user: AdminUser
  restaurants: AdminRestaurant[]
  urls: AdminUrls
  restaurant: EditorDashboard
}
